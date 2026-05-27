import { Jurisdiction } from './types';
import { STATES } from './states';
import { CURATED_STATE_RESOURCES } from './curatedResources';
import { SEED_COUNTIES } from './counties';
import { FEDERAL_JURISDICTION } from './federal';

/** Build a safe Google search URL for finding an official lookup. */
export function buildSearchFallbackUrl(jurisdictionName: string): string {
  const query = `${jurisdictionName} official active warrant search`;
  return `https://www.google.com/search?q=${encodeURIComponent(query)}`;
}

const STATE_JURISDICTIONS: Jurisdiction[] = STATES.map((s) => {
  const resources = CURATED_STATE_RESOURCES[s.abbr] ?? [];
  return {
    id: `state-${s.abbr.toLowerCase()}`,
    name: s.name,
    level: 'state' as const,
    state: s.abbr,
    resources,
    verified: resources.some((r) => r.confidence === 'high'),
    note:
      'Most states have no public "active warrant" search — bench/arrest warrants usually appear within a court case docket or are published by the county sheriff. Start here, then drill down to the relevant county.',
  };
});

export const ALL_JURISDICTIONS: Jurisdiction[] = [
  FEDERAL_JURISDICTION,
  ...STATE_JURISDICTIONS,
  ...SEED_COUNTIES,
];

/** Federal pinned first, then all states — the default directory listing. */
const DIRECTORY_ROOT: Jurisdiction[] = [FEDERAL_JURISDICTION, ...STATE_JURISDICTIONS];

const FEDERAL_KEYWORDS = ['federal', 'us marshal', 'marshal', 'fbi', 'pacer', 'fugitive'];

export function getStates(): Jurisdiction[] {
  return STATE_JURISDICTIONS;
}

export function getCountiesForState(stateAbbr: string): Jurisdiction[] {
  return SEED_COUNTIES.filter((c) => c.state === stateAbbr);
}

export function searchJurisdictions(query: string): Jurisdiction[] {
  const q = query.trim().toLowerCase();
  if (!q) return DIRECTORY_ROOT;
  const matchesFederal = FEDERAL_KEYWORDS.some((k) => k.includes(q) || q.includes(k));
  return ALL_JURISDICTIONS.filter(
    (j) =>
      (j.level === 'federal' && matchesFederal) ||
      j.name.toLowerCase().includes(q) ||
      j.state.toLowerCase() === q ||
      j.state.toLowerCase().includes(q),
  );
}

export function getJurisdictionById(id: string): Jurisdiction | undefined {
  return ALL_JURISDICTIONS.find((j) => j.id === id);
}
