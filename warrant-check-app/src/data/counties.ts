import { Confidence, Jurisdiction, WarrantResource } from './types';

interface CountySeed {
  name: string;
  state: string;
  label: string;
  url: string;
  description: string;
  confidence: Confidence;
}

/**
 * Verified county-level warrant / court-record resources for the most-populous
 * counties in the largest states. Compiled from web research of official
 * sources. Coverage is population-weighted and intentionally partial — the US
 * has 3,000+ counties; this list grows over time.
 *
 * Last research pass: 2026-05.
 */
const COUNTY_SEEDS: CountySeed[] = [
  // California
  { name: 'Los Angeles County', state: 'CA', label: 'LASD Inmate Information Center', url: 'https://app5.lasd.org/iic', description: 'Sheriff inmate/warrant lookup, Los Angeles County.', confidence: 'med' },
  { name: 'San Diego County', state: 'CA', label: 'San Diego Sheriff Warrant Search', url: 'https://apps.sdsheriff.net/warrant/', description: 'Active San Diego Superior Court adult warrants.', confidence: 'high' },
  { name: 'Orange County', state: 'CA', label: 'OC Superior Court Case Search', url: 'https://visionpublic.occourts.org/Search.do', description: 'Orange County criminal/traffic case and warrant info.', confidence: 'high' },
  // Florida
  { name: 'Miami-Dade County', state: 'FL', label: 'Criminal Justice Online Case Search', url: 'https://www2.miamidadeclerk.gov/cjis/', description: 'Clerk criminal case search, Miami-Dade County.', confidence: 'high' },
  { name: 'Broward County', state: 'FL', label: 'Broward Clerk of Courts Case Search', url: 'https://www.browardclerk.org/web2', description: 'Public case/warrant search, Broward County.', confidence: 'high' },
  { name: 'Palm Beach County', state: 'FL', label: 'Palm Beach Clerk eCaseView', url: 'https://www.mypalmbeachclerk.com/records/court-records/court-records-search', description: 'Criminal/civil/traffic case search, Palm Beach County.', confidence: 'high' },
  // Georgia
  { name: 'Fulton County', state: 'GA', label: 'Fulton County Sheriff', url: 'https://fcsoga.org/', description: 'In-person warrant check; sheriff is custodian.', confidence: 'med' },
  { name: 'Gwinnett County', state: 'GA', label: 'Gwinnett Sheriff JAIL View', url: 'https://www.gwinnettcountysheriff.org/', description: 'Inmate/warrant search, Gwinnett County.', confidence: 'med' },
  { name: 'Cobb County', state: 'GA', label: 'Cobb Magistrate Warrant Inquiry', url: 'https://www.cobbcounty.gov/magistrate-court/magistrate-court-warrant-division/warrant-inquiry', description: 'Active warrant inquiry, Cobb County.', confidence: 'high' },
  // Illinois
  { name: 'Cook County', state: 'IL', label: 'Cook County Clerk Case Search', url: 'https://casesearch.cookcountyclerkofcourt.org/', description: 'Circuit court case search, Cook County.', confidence: 'high' },
  { name: 'DuPage County', state: 'IL', label: 'DuPage Circuit Clerk Search', url: 'https://epay.dupagecircuitclerk.gov/Clerk/allsearch.do', description: 'Circuit court case search, DuPage County.', confidence: 'high' },
  { name: 'Lake County', state: 'IL', label: 'Lake County Circuit Clerk Access', url: 'https://circuitclerk.lakecountyil.gov/publicAccess/', description: 'Civil/criminal/traffic case search, Lake County.', confidence: 'high' },
  // New York (statewide WebCrims covers NYC counties)
  { name: 'Kings County (Brooklyn)', state: 'NY', label: 'NYS WebCrims Defendant Search', url: 'https://iapps.courts.state.ny.us/webcrim_attorney/DefendantSearch', description: 'Statewide portal covers Brooklyn; warrant status shown.', confidence: 'high' },
  { name: 'Queens County', state: 'NY', label: 'NYS WebCrims Defendant Search', url: 'https://iapps.courts.state.ny.us/webcrim_attorney/DefendantSearch', description: 'Statewide portal covers Queens; warrant status shown.', confidence: 'high' },
  { name: 'New York County (Manhattan)', state: 'NY', label: 'NYS WebCrims Defendant Search', url: 'https://iapps.courts.state.ny.us/webcrim_attorney/DefendantSearch', description: 'Statewide portal covers Manhattan; warrant status shown.', confidence: 'high' },
  // Michigan
  { name: 'Wayne County', state: 'MI', label: 'Wayne County Prosecutor Warrants', url: 'https://www.waynecounty.com/elected/prosecutor/warrants-section.aspx', description: 'County warrant info; no public online repository.', confidence: 'med' },
  { name: 'Oakland County', state: 'MI', label: 'Oakland County Sheriff', url: 'https://www.oakgov.com/community/sheriff', description: 'Sheriff publishes a most-wanted active-warrant list.', confidence: 'med' },
  { name: 'Macomb County', state: 'MI', label: 'Macomb County Sheriff', url: 'https://sheriff.macombgov.org/', description: 'Jail/warrant info for current detainees.', confidence: 'med' },
  // New Jersey
  { name: 'Bergen County', state: 'NJ', label: 'Bergen County Sheriff Warrant Unit', url: 'https://www.bcsd.us/warrant-unit', description: 'Sheriff warrant unit and most-wanted resource.', confidence: 'med' },
  { name: 'Essex County', state: 'NJ', label: 'Essex County Sheriff Most Wanted', url: 'https://www.essexsheriff.com/most-wanted/', description: 'Sheriff publishes outstanding-warrant most-wanted list.', confidence: 'med' },
  { name: 'Middlesex County', state: 'NJ', label: 'NJ Courts Criminal Public Access', url: 'https://portal.njcourts.gov/webe41/ExternalPGPA/CaptchaServlet', description: 'Statewide criminal search covers Middlesex.', confidence: 'med' },
  // North Carolina
  { name: 'Wake County', state: 'NC', label: 'NC eCourts Portal', url: 'https://portal.nccourts.gov/', description: 'Statewide portal covers Wake County.', confidence: 'high' },
  { name: 'Mecklenburg County', state: 'NC', label: 'Mecklenburg Sheriff Warrant Search', url: 'https://mecksheriffweb.mecklenburgcountync.gov/Warrant', description: 'County sheriff warrant/served-warrant search.', confidence: 'high' },
  { name: 'Guilford County', state: 'NC', label: "Guilford County Sheriff's Office", url: 'https://www.guilfordcountync.gov/government/sheriffs-office', description: 'Sheriff warrant info and most-wanted list.', confidence: 'med' },
  // Texas
  { name: 'Harris County', state: 'TX', label: 'Harris County Sheriff Warrant Search', url: 'https://harriscountyso.org/JailInfo/warrantssearch', description: 'Class A/B misdemeanor warrants, updated daily.', confidence: 'high' },
  { name: 'Dallas County', state: 'TX', label: 'Dallas County Wanted Search', url: 'https://www.dallascounty.org/dcwantedsearch/search.jsp', description: 'Official county active warrant/wanted search.', confidence: 'high' },
  { name: 'Tarrant County', state: 'TX', label: 'Tarrant County Sheriff Criminal Warrants', url: 'https://www.tarrantcountytx.gov/en/sheriff/operations-bureau/criminal-investigations/criminal-warrants.html', description: 'Criminal warrant info; limited public online release.', confidence: 'med' },
  // Pennsylvania
  { name: 'Philadelphia County', state: 'PA', label: 'First Judicial District (Philadelphia)', url: 'https://www.courts.phila.gov/', description: 'County court system; use UJS portal for warrants.', confidence: 'med' },
  { name: 'Allegheny County', state: 'PA', label: 'Allegheny County Criminal Records', url: 'https://www.alleghenycounty.us/Government/Court-Related/Criminal-Records', description: 'County criminal records; UJS portal for dockets.', confidence: 'high' },
  { name: 'Montgomery County', state: 'PA', label: 'PA UJS Web Portal', url: 'https://ujsportal.pacourts.us/casesearch', description: 'Statewide portal covers Montgomery County dockets.', confidence: 'med' },
  // Ohio
  { name: 'Franklin County', state: 'OH', label: 'Franklin County Clerk Case Info Online', url: 'https://fcdcfcjs.co.franklin.oh.us/CaseInformationOnline/', description: 'Common Pleas case search; municipal shows warrant status.', confidence: 'high' },
  { name: 'Cuyahoga County', state: 'OH', label: 'Cuyahoga Clerk of Courts Docket', url: 'https://cpdocket.cp.cuyahogacounty.gov/Search.aspx', description: 'Common Pleas case and docket records search.', confidence: 'high' },
  { name: 'Hamilton County', state: 'OH', label: 'Hamilton County Clerk Records Search', url: 'https://www.courtclerk.org/records-search/', description: 'County court records search: civil/criminal/traffic.', confidence: 'high' },
];

function slug(s: string): string {
  return s.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
}

export const SEED_COUNTIES: Jurisdiction[] = COUNTY_SEEDS.map((c) => {
  const resource: WarrantResource = {
    label: c.label,
    url: c.url,
    description: c.description,
    confidence: c.confidence,
  };
  return {
    id: `county-${c.state.toLowerCase()}-${slug(c.name)}`,
    name: c.name,
    level: 'county',
    state: c.state,
    parentId: `state-${c.state.toLowerCase()}`,
    resources: [resource],
    verified: c.confidence === 'high',
  };
});
