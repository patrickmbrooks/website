import type { FastifyInstance } from 'fastify';
import { z } from 'zod';
import type { Config } from '../config.js';
import { requireAuth } from '../auth.js';
import { requireProEntitlement } from '../entitlement.js';
import { badRequest, notFound } from '../lib/errors.js';
import type { ConsentRepo } from '../repositories/consentRepo.js';
import type { ReportsRepo } from '../repositories/reportsRepo.js';
import type { CraProvider } from '../providers/types.js';
import { isTerminal } from '../providers/types.js';

const CreateBody = z.object({
  subject: z.object({
    firstName: z.string().min(1),
    lastName: z.string().min(1),
    email: z.string().email().optional(),
    dateOfBirth: z.string().regex(/^\d{4}-\d{2}-\d{2}$/).optional(),
  }),
  purpose: z.enum(['employment', 'tenant', 'volunteer', 'other']),
  consent: z.object({
    signedAt: z.string().datetime(),
    disclosureText: z.string().min(1),
  }),
});

interface Deps {
  cfg: Config;
  consents: ConsentRepo;
  reports: ReportsRepo;
  provider: CraProvider;
}

export function registerBackgroundCheckRoutes(app: FastifyInstance, deps: Deps): void {
  const { cfg, consents, reports, provider } = deps;

  app.post('/v1/background-checks', async (req, reply) => {
    const caller = requireAuth(cfg, req);
    await requireProEntitlement(cfg, caller.requesterId);

    const parsed = CreateBody.safeParse(req.body);
    if (!parsed.success) throw badRequest(parsed.error.issues.map((i) => i.message).join('; '));
    const body = parsed.data;

    const ip = (req.headers['x-forwarded-for'] as string | undefined)?.split(',')[0]?.trim() ?? req.ip;

    const consent = consents.create({
      requesterId: caller.requesterId,
      subjectFirstName: body.subject.firstName,
      subjectLastName: body.subject.lastName,
      subjectEmail: body.subject.email,
      purpose: body.purpose,
      disclosureText: body.consent.disclosureText,
      signedAt: body.consent.signedAt,
      ipAddress: ip,
    });

    const created = await provider.createReport({
      firstName: body.subject.firstName,
      lastName: body.subject.lastName,
      email: body.subject.email,
      dateOfBirth: body.subject.dateOfBirth,
      purpose: body.purpose,
      consentId: consent.id,
    });

    const report = reports.create({
      consentId: consent.id,
      requesterId: caller.requesterId,
      provider: provider.name,
      providerReportId: created.providerReportId,
      status: created.status,
    });

    reply.code(202);
    return {
      reportId: report.id,
      status: report.status,
      consentId: consent.id,
      records: report.records,
    };
  });

  app.get<{ Params: { id: string } }>('/v1/background-checks/:id', async (req) => {
    const caller = requireAuth(cfg, req);
    const row = reports.getById(req.params.id);
    if (!row || row.requesterId !== caller.requesterId) throw notFound('Report not found.');

    // If still pending, poll the provider for a fresh status. Cheap safety net
    // in case a webhook was missed. Terminal reports skip the roundtrip.
    if (!isTerminal(row.status)) {
      try {
        const fresh = await provider.getReport(row.providerReportId);
        if (fresh.status !== row.status || fresh.records.length !== row.records.length) {
          const updated = reports.updateStatus(
            row.id,
            fresh.status,
            fresh.records,
            isTerminal(fresh.status),
          );
          return toApi(updated);
        }
      } catch {
        // Fall through and return the last known state.
      }
    }
    return toApi(row);
  });
}

function toApi(row: {
  id: string;
  status: string;
  records: unknown[];
  createdAt: string;
  updatedAt: string;
  completedAt: string | null;
}) {
  return {
    reportId: row.id,
    status: row.status,
    records: row.records,
    createdAt: row.createdAt,
    updatedAt: row.updatedAt,
    completedAt: row.completedAt,
  };
}
