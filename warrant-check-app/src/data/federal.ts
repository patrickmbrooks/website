import { Jurisdiction } from './types';

/**
 * Federal-level resources.
 *
 * IMPORTANT: Active federal ARREST warrants are generally SEALED and are NOT
 * publicly searchable — by design, so fugitives are not tipped off. There is no
 * public "look up my federal warrant" tool. What is public:
 *   - Published fugitive / most-wanted listings (U.S. Marshals, FBI).
 *   - Federal court CASE records via PACER (a warrant may appear on a docket
 *     once unsealed, but PACER is not a warrant search and charges fees).
 * The directory reflects this honestly rather than implying coverage that
 * does not exist.
 *
 * Last research pass: 2026-05.
 */
export const FEDERAL_JURISDICTION: Jurisdiction = {
  id: 'federal-us',
  name: 'Federal (United States)',
  level: 'federal',
  state: 'US',
  verified: true,
  note:
    'There is no public search for active federal arrest warrants — they are typically sealed until executed. These resources cover published fugitives and federal court case records only.',
  resources: [
    {
      label: 'U.S. Marshals Service — Fugitive Investigations',
      url: 'https://www.usmarshals.gov/what-we-do/fugitive-investigations',
      description: 'Federal fugitive program and 15 Most Wanted; report tips at 1-800-336-0102.',
      confidence: 'high',
    },
    {
      label: 'FBI Most Wanted',
      url: 'https://www.fbi.gov/wanted',
      description: 'FBI wanted persons, fugitives, and Ten Most Wanted listings.',
      confidence: 'high',
    },
    {
      label: 'PACER — Federal Court Records',
      url: 'https://pacer.uscourts.gov/',
      description: 'Federal district/appellate case dockets (fees apply); not a warrant search.',
      confidence: 'high',
    },
  ],
};
