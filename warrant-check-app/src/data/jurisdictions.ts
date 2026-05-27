import { Jurisdiction } from './types';
import { STATES } from './states';
import { CURATED_STATE_RESOURCES } from './curatedResources';
import { SEED_COUNTIES } from './counties';

/** Build a safe Google search URL for finding an official lookup. */
export function buildSearchFallbackUrl(jurisdictionName: string): string {
  const query = `${jurisdictionName} official active warrant search`;
  return `https://www.google.com/search?q=${encodeURIComponent(query)}`;
}

const STATE_JURISDICTIONS: Jurisdiction[] = STATES.map((s) => ({
  id: `state-${s.abbr.toLowerCase()}`,
  name: s.name,
  level: 'state',
  state: s.abbr,
  resources: CURATED_STATE_RESOURCES[s.abbr] ?? [],
  verified: false,
  note:
    'Outstanding warrants are usually published at the county sheriff or local court level. Start here, then drill down to the relevant county.',
}));

export const ALL_JURISDICTIONS: Jurisdiction[] = [
  ...STATE_JURISDICTIONS,
  ...SEED_COUNTIES,
];

export function getStates(): Jurisdiction[] {
  return STATE_JURISDICTIONS;
}

export function getCountiesForState(stateAbbr: string): Jurisdiction[] {
  return SEED_COUNTIES.filter((c) => c.state === stateAbbr);
}

export function searchJurisdictions(query: string): Jurisdiction[] {
  const q = query.trim().toLowerCase();
  if (!q) return STATE_JURISDICTIONS;
  return ALL_JURISDICTIONS.filter(
    (j) =>
      j.name.toLowerCase().includes(q) ||
      j.state.toLowerCase() === q ||
      j.state.toLowerCase().includes(q),
  );
}

export function getJurisdictionById(id: string): Jurisdiction | undefined {
  return ALL_JURISDICTIONS.find((j) => j.id === id);
}
