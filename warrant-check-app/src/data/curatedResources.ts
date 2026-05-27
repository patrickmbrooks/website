import { WarrantResource } from './types';

/**
 * Curated statewide court / case-search portals.
 *
 * IMPORTANT: These are official state judiciary portals (stable .gov / .us
 * domains). They are starting points for case and record searches, NOT
 * guaranteed dedicated "active warrant" lookups — outstanding warrants in
 * most states are published at the COUNTY sheriff / clerk level, not
 * statewide. Every entry is intentionally left `verified: false` in the
 * Jurisdiction record until a human confirms the deep link and coverage.
 *
 * States not listed here have no curated portal yet and fall back to a
 * runtime search link in the UI.
 */
export const CURATED_STATE_RESOURCES: Record<string, WarrantResource[]> = {
  AZ: [
    {
      label: 'Arizona Judicial Branch — Case Search',
      url: 'https://www.azcourts.gov',
      description: 'Statewide courts portal. Use the public case lookup; many warrants are issued at the county/justice-court level.',
    },
  ],
  CA: [
    {
      label: 'California Courts',
      url: 'https://www.courts.ca.gov',
      description: 'Statewide courts portal. Warrant and case records are maintained by each Superior Court (by county).',
    },
  ],
  CO: [
    {
      label: 'Colorado Judicial Branch',
      url: 'https://www.coloradojudicial.gov',
      description: 'Statewide courts portal. Record searches may require the county district court.',
    },
  ],
  CT: [
    {
      label: 'Connecticut Judicial Branch',
      url: 'https://www.jud.ct.gov',
      description: 'Statewide courts portal with online case look-up tools.',
    },
  ],
  FL: [
    {
      label: 'Florida State Courts',
      url: 'https://www.flcourts.gov',
      description: 'Statewide courts portal. Warrant lists are typically published by each county Clerk of Court and Sheriff.',
    },
  ],
  GA: [
    {
      label: 'Georgia Courts',
      url: 'https://georgiacourts.gov',
      description: 'Statewide courts portal. Warrants are generally handled at the county level.',
    },
  ],
  IL: [
    {
      label: 'Illinois Courts',
      url: 'https://www.illinoiscourts.gov',
      description: 'Statewide courts portal. Case records are maintained by each Circuit Court (by county).',
    },
  ],
  MD: [
    {
      label: 'Maryland Judiciary Case Search',
      url: 'https://www.mdcourts.gov',
      description: 'Statewide judiciary portal that links to the public Case Search system.',
    },
  ],
  MI: [
    {
      label: 'Michigan Courts',
      url: 'https://www.courts.michigan.gov',
      description: 'Statewide courts portal. Record searches may route to the county trial court.',
    },
  ],
  MN: [
    {
      label: 'Minnesota Judicial Branch',
      url: 'https://www.mncourts.gov',
      description: 'Statewide courts portal with public access case records.',
    },
  ],
  NJ: [
    {
      label: 'New Jersey Courts',
      url: 'https://www.njcourts.gov',
      description: 'Statewide courts portal with public case search tools.',
    },
  ],
  NY: [
    {
      label: 'New York State Unified Court System',
      url: 'https://www.nycourts.gov',
      description: 'Statewide courts portal. Criminal case look-ups (WebCrims) are organized by county.',
    },
  ],
  OH: [
    {
      label: 'Supreme Court of Ohio',
      url: 'https://www.supremecourt.ohio.gov',
      description: 'Statewide courts portal. Case and warrant records are maintained by each county Clerk of Courts.',
    },
  ],
  OR: [
    {
      label: 'Oregon Judicial Department',
      url: 'https://www.courts.oregon.gov',
      description: 'Statewide courts portal with Oregon eCourt record search.',
    },
  ],
  PA: [
    {
      label: 'Pennsylvania Unified Judicial System Web Portal',
      url: 'https://ujsportal.pacourts.us',
      description: 'Statewide public case search across Pennsylvania courts.',
    },
  ],
  TX: [
    {
      label: 'Texas Judicial Branch',
      url: 'https://www.txcourts.gov',
      description: 'Statewide courts portal. Warrants are generally issued and published at the county level.',
    },
  ],
  VA: [
    {
      label: 'Virginia Judicial System — Online Case Information',
      url: 'https://www.vacourts.gov',
      description: 'Statewide courts portal linking to the public Online Case Information System.',
    },
  ],
  WA: [
    {
      label: 'Washington Courts',
      url: 'https://www.courts.wa.gov',
      description: 'Statewide courts portal with public case search tools.',
    },
  ],
};
