import { createHmac, timingSafeEqual } from 'node:crypto';
import { upstream } from '../lib/errors.js';
import type {
  CraCreateResult,
  CraProvider,
  CraRecord,
  CraReport,
  CraStatus,
  CraWebhookEvent,
  CreateReportInput,
} from './types.js';

/**
 * Checkr driver skeleton.
 *
 * Real Checkr flow (simplified):
 *   1. POST /v1/candidates    → create candidate from subject info + purpose ("package").
 *   2. POST /v1/reports       → create report for the candidate; body includes
 *      the "package" (e.g. "tasker_standard") and the candidate id.
 *   3. Webhook events (report.created / .completed / .suspended) update status.
 *   4. GET /v1/reports/:id    → poll for status if webhooks aren't wired.
 *
 * Checkr requires a signed data-use agreement, credentialed-user setup, and a
 * disclosure/authorization flow before granting live API access. Use TEST keys
 * in dev — they never pull real consumer data.
 */
export class CheckrProvider implements CraProvider {
  readonly name = 'checkr';

  constructor(
    private readonly apiKey: string,
    private readonly webhookSecret: string,
    private readonly baseUrl: string = 'https://api.checkr.com',
  ) {}

  private authHeader(): string {
    // Checkr uses HTTP Basic with the API key as the username, blank password.
    return `Basic ${Buffer.from(`${this.apiKey}:`).toString('base64')}`;
  }

  private async request<T>(path: string, init: RequestInit = {}): Promise<T> {
    const res = await fetch(`${this.baseUrl}${path}`, {
      ...init,
      headers: {
        Authorization: this.authHeader(),
        'Content-Type': 'application/json',
        ...(init.headers ?? {}),
      },
    });
    if (!res.ok) {
      const text = await res.text().catch(() => '');
      throw upstream(`Checkr ${res.status}: ${text || res.statusText}`);
    }
    return (await res.json()) as T;
  }

  async createReport(input: CreateReportInput): Promise<CraCreateResult> {
    // Step 1: candidate
    const candidate = await this.request<{ id: string }>('/v1/candidates', {
      method: 'POST',
      body: JSON.stringify({
        first_name: input.firstName,
        last_name: input.lastName,
        email: input.email,
        dob: input.dateOfBirth,
        // Attach our internal consent id so it round-trips in Checkr's dashboard.
        custom_id: input.consentId,
      }),
    });

    // Step 2: report. Package selection is business-specific — swap as needed.
    const pkg = input.purpose === 'tenant' ? 'tasker_tenant' : 'tasker_standard';
    const report = await this.request<{ id: string; status: string }>('/v1/reports', {
      method: 'POST',
      body: JSON.stringify({ candidate_id: candidate.id, package: pkg }),
    });

    return {
      providerReportId: report.id,
      status: normalizeStatus(report.status),
    };
  }

  async getReport(providerReportId: string): Promise<CraReport> {
    const report = await this.request<{
      id: string;
      status: string;
      completed_at?: string;
      // Real Checkr responses include arrays like criminal_searches, motor_vehicle_reports, etc.
      // A production integration would flatten those into CraRecord[]. This skeleton returns [].
    }>(`/v1/reports/${encodeURIComponent(providerReportId)}`);
    return {
      providerReportId: report.id,
      status: normalizeStatus(report.status),
      records: [],
      completedAt: report.completed_at,
    };
  }

  /**
   * Checkr signs webhooks with HMAC-SHA256 in the `X-Checkr-Signature` header.
   * Compare in constant time against the shared secret and the raw body bytes.
   */
  verifyWebhook(rawBody: Buffer, headers: Record<string, string>): boolean {
    const provided = headers['x-checkr-signature'] ?? '';
    if (!provided || !this.webhookSecret) return false;
    const expected = createHmac('sha256', this.webhookSecret).update(rawBody).digest('hex');
    if (expected.length !== provided.length) return false;
    try {
      return timingSafeEqual(Buffer.from(expected), Buffer.from(provided));
    } catch {
      return false;
    }
  }

  parseWebhookEvent(body: unknown): CraWebhookEvent {
    const b = body as {
      type?: string;
      data?: { object?: { id?: string; status?: string } };
    };
    const obj = b.data?.object;
    return {
      providerReportId: String(obj?.id ?? ''),
      status: normalizeStatus(obj?.status ?? 'pending'),
      records: extractRecordsPlaceholder(),
    };
  }
}

function normalizeStatus(s: string): CraStatus {
  switch (s) {
    case 'pending':
    case 'clear':
    case 'consider':
    case 'complete':
    case 'suspended':
      return s;
    default:
      return 'error';
  }
}

/**
 * Real Checkr reports embed structured screening arrays (national_criminal_search,
 * county_criminal_searches, motor_vehicle_report, etc). Flattening those into
 * CraRecord[] is business-specific — do it in a follow-up once you know which
 * screenings you actually purchase.
 */
function extractRecordsPlaceholder(): CraRecord[] {
  return [];
}
