import { Jurisdiction } from './types';

/**
 * Seed set of high-population counties to demonstrate the county layer.
 * The directory is designed so counties can be added incrementally; the US
 * has ~3,000+ counties and tens of thousands of municipal courts, so this
 * list is intentionally partial and clearly marked unverified.
 */
export const SEED_COUNTIES: Jurisdiction[] = [
  {
    id: 'county-ca-los-angeles',
    name: 'Los Angeles County',
    level: 'county',
    state: 'CA',
    parentId: 'state-ca',
    resources: [
      {
        label: 'LA County Sheriff',
        url: 'https://lasd.org',
        description: 'County sheriff site. Check the inmate/warrant information sections.',
      },
    ],
    verified: false,
  },
  {
    id: 'county-il-cook',
    name: 'Cook County',
    level: 'county',
    state: 'IL',
    parentId: 'state-il',
    resources: [
      {
        label: 'Cook County Sheriff',
        url: 'https://www.cookcountysheriff.org',
        description: 'County sheriff site with warrant and inmate information.',
      },
    ],
    verified: false,
  },
  {
    id: 'county-tx-harris',
    name: 'Harris County',
    level: 'county',
    state: 'TX',
    parentId: 'state-tx',
    resources: [
      {
        label: 'Harris County Sheriff / Constable',
        url: 'https://www.harriscountyso.org',
        description: 'County sheriff site. Harris County also publishes a public warrant search.',
      },
    ],
    verified: false,
  },
  {
    id: 'county-az-maricopa',
    name: 'Maricopa County',
    level: 'county',
    state: 'AZ',
    parentId: 'state-az',
    resources: [
      {
        label: 'Maricopa County Sheriff',
        url: 'https://www.mcso.org',
        description: 'County sheriff site with warrant information.',
      },
    ],
    verified: false,
  },
  {
    id: 'county-fl-miami-dade',
    name: 'Miami-Dade County',
    level: 'county',
    state: 'FL',
    parentId: 'state-fl',
    resources: [
      {
        label: 'Miami-Dade Clerk of Courts',
        url: 'https://www.miamidadeclerk.gov',
        description: 'County clerk site with online case and record search.',
      },
    ],
    verified: false,
  },
];
