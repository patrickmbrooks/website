/**
 * Background-check provider abstraction.
 *
 * Real implementations should call a FCRA-compliant Consumer Reporting Agency
 * (e.g. Checkr, Sterling, Accurate) from a SECURE BACKEND — never embed
 * provider API keys in the mobile client, and never expose raw consumer
 * reports without the required disclosures and adverse-action workflow.
 *
 * This is a typed stub returning mock data so the screening UI is testable.
 */

export interface BackgroundCheckRequest {
  firstName: string;
  lastName: string;
  /** Confirmation that FCRA disclosure + written consent are on file. */
  consentConfirmed: boolean;
  /** Permissible purpose, e.g. "employment". */
  purpose: 'employment' | 'tenant' | 'volunteer' | 'other';
}

export interface BackgroundCheckRecord {
  type: 'warrant' | 'criminal' | 'court';
  jurisdiction: string;
  description: string;
  date?: string;
}

export interface BackgroundCheckResult {
  reportId: string;
  status: 'clear' | 'records_found' | 'pending';
  records: BackgroundCheckRecord[];
  disclaimer: string;
}

export async function runBackgroundCheck(
  req: BackgroundCheckRequest,
): Promise<BackgroundCheckResult> {
  if (!req.consentConfirmed) {
    throw new Error('FCRA consent must be confirmed before running a report.');
  }
  // Replace with a call to your backend, which calls the CRA provider.
  await new Promise((r) => setTimeout(r, 900));
  return {
    reportId: `mock_${Date.now()}`,
    status: 'pending',
    records: [],
    disclaimer:
      'MOCK RESULT — no real data source is connected. Wire runBackgroundCheck() ' +
      'to your FCRA-compliant provider backend before relying on results.',
  };
}
