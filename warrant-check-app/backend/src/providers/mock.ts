import { randomUUID } from 'node:crypto';
import type {
  CraCreateResult,
  CraProvider,
  CraReport,
  CraWebhookEvent,
  CreateReportInput,
} from './types.js';

/**
 * Mock CRA driver. Never touches a real network. Returns 'pending' on create
 * and flips to 'clear' with no records once ~5s have elapsed since the report
 * was created (encoded in the id). Useful for local development and CI.
 */
export class MockCraProvider implements CraProvider {
  readonly name = 'mock';

  async createReport(_input: CreateReportInput): Promise<CraCreateResult> {
    const providerReportId = `mock_${Date.now()}_${randomUUID().slice(0, 8)}`;
    return { providerReportId, status: 'pending' };
  }

  async getReport(providerReportId: string): Promise<CraReport> {
    const createdMs = Number(providerReportId.split('_')[1] ?? Date.now());
    const elapsed = Date.now() - createdMs;
    const done = elapsed > 5_000;
    return {
      providerReportId,
      status: done ? 'clear' : 'pending',
      records: [],
      completedAt: done ? new Date().toISOString() : undefined,
    };
  }

  verifyWebhook(): boolean {
    return true;
  }

  parseWebhookEvent(body: unknown): CraWebhookEvent {
    const b = body as { providerReportId?: string; status?: string };
    return {
      providerReportId: String(b.providerReportId ?? ''),
      status: (b.status as CraWebhookEvent['status']) ?? 'pending',
    };
  }
}
