import type { DB } from '../db.js';
import { randomUUID } from 'node:crypto';
import type { CraRecord, CraStatus } from '../providers/types.js';

export interface ReportRow {
  id: string;
  consentId: string;
  requesterId: string;
  provider: string;
  providerReportId: string;
  status: CraStatus;
  records: CraRecord[];
  createdAt: string;
  updatedAt: string;
  completedAt: string | null;
}

interface Raw {
  id: string;
  consent_id: string;
  requester_id: string;
  provider: string;
  provider_report_id: string;
  status: CraStatus;
  records_json: string;
  created_at: string;
  updated_at: string;
  completed_at: string | null;
}

function hydrate(raw: Raw): ReportRow {
  return {
    id: raw.id,
    consentId: raw.consent_id,
    requesterId: raw.requester_id,
    provider: raw.provider,
    providerReportId: raw.provider_report_id,
    status: raw.status,
    records: JSON.parse(raw.records_json) as CraRecord[],
    createdAt: raw.created_at,
    updatedAt: raw.updated_at,
    completedAt: raw.completed_at,
  };
}

export interface CreateReportInput {
  consentId: string;
  requesterId: string;
  provider: string;
  providerReportId: string;
  status: CraStatus;
}

export class ReportsRepo {
  constructor(private db: DB) {}

  create(input: CreateReportInput): ReportRow {
    const id = randomUUID();
    this.db
      .prepare(
        `INSERT INTO reports (id, consent_id, requester_id, provider, provider_report_id, status)
         VALUES (?, ?, ?, ?, ?, ?)`,
      )
      .run(id, input.consentId, input.requesterId, input.provider, input.providerReportId, input.status);
    return this.getById(id)!;
  }

  getById(id: string): ReportRow | null {
    const row = this.db.prepare(`SELECT * FROM reports WHERE id = ?`).get(id) as Raw | undefined;
    return row ? hydrate(row) : null;
  }

  findByProviderReport(provider: string, providerReportId: string): ReportRow | null {
    const row = this.db
      .prepare(`SELECT * FROM reports WHERE provider = ? AND provider_report_id = ?`)
      .get(provider, providerReportId) as Raw | undefined;
    return row ? hydrate(row) : null;
  }

  updateStatus(id: string, status: CraStatus, records: CraRecord[], completed: boolean): ReportRow {
    this.db
      .prepare(
        `UPDATE reports
         SET status = ?, records_json = ?, updated_at = datetime('now'),
             completed_at = CASE WHEN ? = 1 THEN datetime('now') ELSE completed_at END
         WHERE id = ?`,
      )
      .run(status, JSON.stringify(records), completed ? 1 : 0, id);
    return this.getById(id)!;
  }
}
