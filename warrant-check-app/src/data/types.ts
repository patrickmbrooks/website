export type JurisdictionLevel = 'state' | 'county' | 'city';

export type Confidence = 'high' | 'med' | 'low';

export interface WarrantResource {
  /** Human-readable label, e.g. "Statewide Case Search". */
  label: string;
  /** Official URL. */
  url: string;
  /** What this resource actually covers, in plain language. */
  description: string;
  /**
   * How confident we are this is the correct official resource:
   * 'high' — exact official .gov URL confirmed via research.
   * 'med'  — plausible official source, deep link not fully confirmed.
   * 'low'  — unverified.
   */
  confidence: Confidence;
}

export interface Jurisdiction {
  id: string;
  name: string;
  level: JurisdictionLevel;
  /** Two-letter state abbreviation this jurisdiction belongs to. */
  state: string;
  /** Parent jurisdiction id (e.g. a county's state). */
  parentId?: string;
  /**
   * Official warrant / court-record lookup resources.
   * Empty means no known public online lookup for this jurisdiction.
   */
  resources: WarrantResource[];
  /**
   * Whether the URLs in `resources` have been hand-verified.
   * Unverified entries fall back to a search link in the UI.
   */
  verified: boolean;
  /** Optional plain-language note shown to the user. */
  note?: string;
}
