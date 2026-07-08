import type { FastifyInstance } from 'fastify';
import { forbidden, notFound } from '../lib/errors.js';
import type { CraProvider } from '../providers/types.js';
import { isTerminal } from '../providers/types.js';
import type { ReportsRepo } from '../repositories/reportsRepo.js';

interface Deps {
  provider: CraProvider;
  reports: ReportsRepo;
}

export function registerWebhookRoutes(app: FastifyInstance, deps: Deps): void {
  const { provider, reports } = deps;

  /**
   * Provider webhook. The raw body is required for signature verification, so
   * this route parses as Buffer via the config parser below.
   */
  app.post<{ Params: { name: string }; Body: Buffer }>('/v1/webhooks/:name', async (req, reply) => {
    if (req.params.name !== provider.name) {
      throw notFound(`Unknown webhook provider: ${req.params.name}`);
    }
    const rawBody = req.body;
    const headers = Object.fromEntries(
      Object.entries(req.headers).map(([k, v]) => [k.toLowerCase(), Array.isArray(v) ? (v[0] ?? '') : String(v ?? '')]),
    );
    if (!provider.verifyWebhook(rawBody, headers)) {
      throw forbidden('Invalid webhook signature.');
    }

    let parsedBody: unknown;
    try {
      parsedBody = JSON.parse(rawBody.toString('utf8'));
    } catch {
      parsedBody = {};
    }
    const event = provider.parseWebhookEvent(parsedBody);
    if (!event.providerReportId) {
      reply.code(204);
      return;
    }

    const row = reports.findByProviderReport(provider.name, event.providerReportId);
    if (row) {
      reports.updateStatus(row.id, event.status, event.records ?? row.records, isTerminal(event.status));
    }
    reply.code(200);
    return { ok: true };
  });
}
