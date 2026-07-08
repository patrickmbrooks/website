export type CraStatus = 'pending' | 'clear' | 'consider' | 'complete' | 'suspended' | 'error';

export interface CraRecord {
  type: 'warrant' | 'criminal' | 'court' | 'traffic' | 'other';
  jurisdiction?: string;
  description: string;
  date?: string;
}

export interface CreateReportInput {
  firstName: string;
  lastName: string;
  email?: string;
  dateOfBirth?: string;
  purpose: 'employment' | 'tenant' | 'volunteer' | 'other';
  /** Audit trail reference — the consent record stored by our backend. */
  consentId: string;
}

export interface CraCreateResult {
  providerReportId: string;
  status: CraStatus;
}

export interface CraReport {
  providerReportId: string;
  status: CraStatus;
  records: CraRecord[];
  completedAt?: string;
}

export interface CraWebhookEvent {
  providerReportId: string;
  status: CraStatus;
  records?: CraRecord[];
}

/** Any FCRA-compliant consumer reporting agency driver plugs in here. */
export interface CraProvider {
  readonly name: string;
  createReport(input: CreateReportInput): Promise<CraCreateResult>;
  getReport(providerReportId: string): Promise<CraReport>;
  verifyWebhook(rawBody: Buffer, headers: Record<string, string>): boolean;
  parseWebhookEvent(body: unknown): CraWebhookEvent;
}

export function isTerminal(status: CraStatus): boolean {
  return status === 'clear' || status === 'consider' || status === 'complete' || status === 'suspended' || status === 'error';
}
