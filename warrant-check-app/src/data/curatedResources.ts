import { WarrantResource } from './types';

/**
 * Official statewide warrant / court-record search resources, one per state.
 *
 * Compiled from web research of official government sources (state judiciary
 * case-search portals, state police / DPS tools, and clerk systems). Each
 * resource carries a `confidence` level:
 *   high — exact official URL confirmed and clearly the right resource.
 *   med  — official source, but no public statewide warrant search exists
 *          (warrants are county-level) or the deep link wasn't fully confirmed.
 *
 * NOTE: Most states do NOT publish a dedicated public "active warrant" search.
 * In those states the listed portal is the authoritative court case-search,
 * where bench/arrest warrants may appear within case dockets. Verify each URL
 * and re-confirm coverage periodically — government portals move.
 *
 * Last research pass: 2026-05.
 */
export const CURATED_STATE_RESOURCES: Record<string, WarrantResource[]> = {
  AL: [{ label: 'Alacourt ACCESS public portal', url: 'https://pa.alacourt.com/', description: 'Statewide trial court records, all 67 counties (subscription).', confidence: 'high' }],
  AK: [{ label: 'Alaska CourtView Case Search', url: 'https://courts.alaska.gov/main/search-cases.htm', description: 'Free statewide trial court name/case index.', confidence: 'high' }],
  AZ: [{ label: 'Arizona Public Access Case Lookup', url: 'https://apps.azcourts.gov/publicaccess/caselookup.aspx', description: 'Statewide case search, most courts, free.', confidence: 'high' }],
  AR: [{ label: 'Arkansas CourtConnect', url: 'https://caseinfo.arcourts.gov/opad', description: 'Statewide public case information portal.', confidence: 'high' }],
  CA: [{ label: 'California Find Your Court', url: 'https://courts.ca.gov/', description: 'No statewide search; warrants are county-level via Superior Courts.', confidence: 'med' }],
  CO: [{ label: 'Colorado Docket Search', url: 'https://www.courts.state.co.us/dockets/', description: 'Free statewide district/county court case search.', confidence: 'high' }],
  CT: [{ label: 'Connecticut Criminal/MV Case Look-up', url: 'https://www.jud.ct.gov/crim.htm', description: 'Statewide criminal case and arrest warrant search.', confidence: 'high' }],
  DE: [{ label: 'Delaware CourtConnect', url: 'https://courts.delaware.gov/docket.aspx', description: 'Statewide civil/criminal dockets; warrants via clerk.', confidence: 'med' }],
  DC: [{ label: 'DC Superior Court eAccess', url: 'https://eaccess.dccourts.gov/eaccess/home.page', description: 'DC Superior Court case search (criminal needs registration).', confidence: 'high' }],
  FL: [{ label: 'Florida Courts (county clerk records)', url: 'https://www.flcourts.gov/', description: 'Warrants county-level; clerks and FDLE provide search.', confidence: 'med' }],
  GA: [{ label: 'Georgia Courts E-Access', url: 'https://georgiacourts.gov/eaccess-court-records/', description: 'Directory to county court records; warrants county-level.', confidence: 'med' }],
  HI: [{ label: 'Hawaii eCourt Kokua', url: 'https://www.courts.state.hi.us/legal_references/records/search_court_records', description: 'Statewide criminal/civil/traffic case search.', confidence: 'high' }],
  ID: [{ label: 'Idaho iCourt Portal Smart Search', url: 'https://mycourts.idaho.gov/', description: 'Free statewide case search, all counties, 1995–present.', confidence: 'high' }],
  IL: [{ label: 'Illinois Courts', url: 'https://www.illinoiscourts.gov/', description: 'No unified statewide search; warrants county-level.', confidence: 'med' }],
  IN: [{ label: 'Indiana MyCase', url: 'https://mycase.in.gov/', description: 'Free statewide trial court case search.', confidence: 'high' }],
  IA: [{ label: 'Iowa Courts Online Search', url: 'https://www.iowacourts.state.ia.us/', description: 'Statewide trial/appellate case search (registration).', confidence: 'high' }],
  KS: [{ label: 'Kansas CaseSearch', url: 'https://casesearch.kscourts.gov/', description: 'Statewide district court public case information.', confidence: 'high' }],
  KY: [{ label: 'Kentucky Court of Justice Search', url: 'https://www.kycourts.gov/pages/search.aspx', description: 'Statewide court case search; warrants flagged in CourtNet.', confidence: 'high' }],
  LA: [{ label: 'Louisiana State Police Background Check', url: 'https://ibc.dps.louisiana.gov/', description: 'Name-based state check; warrants parish-level.', confidence: 'med' }],
  ME: [{ label: 'Maine re:SearchMaine eCourts', url: 'https://www.courts.maine.gov/ecourts/', description: 'Statewide eCourts case search; free registration required.', confidence: 'high' }],
  MD: [{ label: 'Maryland Judiciary Case Search', url: 'https://casesearch.courts.state.md.us/', description: 'Free statewide search; shows bench warrant entries.', confidence: 'high' }],
  MA: [{ label: 'MassCourts Trial Court Access', url: 'https://www.masscourts.org/', description: 'Statewide case search; active warrant list not public.', confidence: 'high' }],
  MI: [{ label: 'Michigan MiCOURT Case Search', url: 'https://micourt.courts.michigan.gov/case-search/', description: 'Statewide case search; does not show warrant status.', confidence: 'high' }],
  MN: [{ label: 'Minnesota Court Records Online (MCRO)', url: 'https://publicaccess.courts.state.mn.us/CaseSearch', description: 'Statewide district court search; active-warrant flag shown.', confidence: 'high' }],
  MS: [{ label: 'Mississippi Electronic Courts (MEC)', url: 'https://courts.ms.gov/mec/mec.php', description: 'Statewide trial court records; warrants visible (subscription).', confidence: 'high' }],
  MO: [{ label: 'Missouri Case.net', url: 'https://www.courts.mo.gov/casenet/welcome.do', description: 'Free statewide case search; warrants shown in dockets.', confidence: 'high' }],
  MT: [{ label: 'Montana Courts Public Access Portals', url: 'https://courts.mt.gov/Courts/portals', description: 'Statewide district/limited-jurisdiction case portals.', confidence: 'med' }],
  NE: [{ label: 'Nebraska JUSTICE Case Search', url: 'https://www.nebraska.gov/justicecc/ccname.cgi', description: 'Statewide case search, all 93 counties (paid per-search).', confidence: 'high' }],
  NV: [{ label: 'Nevada Judiciary — Find a Court', url: 'https://nvcourts.gov/', description: 'No unified search; warrants county/municipal-level.', confidence: 'med' }],
  NH: [{ label: 'New Hampshire Case Access Portal', url: 'https://odypa.nhecourt.us/portal/', description: 'Statewide trial court case search (registration).', confidence: 'high' }],
  NJ: [{ label: 'NJ Courts Criminal Public Access', url: 'https://portal.njcourts.gov/webe41/ExternalPGPA/CaptchaServlet', description: 'Statewide indictable criminal case search by name.', confidence: 'high' }],
  NM: [{ label: 'New Mexico Courts Case Lookup', url: 'https://caselookup.nmcourts.gov/caselookup/app', description: 'Statewide case search, all court levels, no registration.', confidence: 'high' }],
  NY: [{ label: 'New York WebCrims Criminal Search', url: 'https://iapps.courts.state.ny.us/webcrim_attorney/AttorneyWelcome', description: 'Statewide criminal cases; warrant-issued status shown.', confidence: 'high' }],
  NC: [{ label: 'North Carolina eCourts Portal', url: 'https://portal.nccourts.gov/', description: 'Statewide case search, all 100 counties, near real-time.', confidence: 'high' }],
  ND: [{ label: 'North Dakota Courts Public Search', url: 'https://publicsearch.ndcourts.gov/', description: 'Statewide district court case search; warrant indicator shown.', confidence: 'high' }],
  OH: [{ label: 'Supreme Court of Ohio Case Lookup', url: 'https://www.supremecourt.ohio.gov/clerk/ecms/', description: 'No statewide warrant DB; warrants issued at county level.', confidence: 'med' }],
  OK: [{ label: 'Oklahoma State Courts Network (OSCN)', url: 'https://www.oscn.net/dockets/Search.aspx', description: 'Statewide court docket and case search, all 77 counties.', confidence: 'high' }],
  OR: [{ label: 'Oregon OJD Online Records (OECI)', url: 'https://webportal.courts.oregon.gov/portal/', description: 'Statewide circuit court case search; warrants county-level.', confidence: 'high' }],
  PA: [{ label: 'Pennsylvania UJS Web Portal', url: 'https://ujsportal.pacourts.us/casesearch', description: 'Statewide court docket search; public warrant data limited.', confidence: 'high' }],
  RI: [{ label: 'Rhode Island Judiciary Public Portal', url: 'https://www.courts.ri.gov/Public-Resources/Pages/case-information.aspx', description: 'Statewide case search including bench warrants.', confidence: 'high' }],
  SC: [{ label: 'South Carolina Case Records Search', url: 'https://www.sccourts.org/case-records-search/', description: 'Statewide public index linking each county; warrants county-level.', confidence: 'high' }],
  SD: [{ label: 'South Dakota UJS Court Records', url: 'https://ujs.sd.gov/cases-and-records/court-records-search/', description: 'Statewide records; PARS criminal search has a fee.', confidence: 'high' }],
  TN: [{ label: 'Tennessee Public Case History', url: 'https://www.tncourts.gov/courts/supreme-court/public-case-history', description: 'Appellate only statewide; trial/warrants via county clerks.', confidence: 'med' }],
  TX: [{ label: 'Texas DPS Failure to Appear', url: 'https://www.texasfailuretoappear.com/', description: 'Statewide FTA/FTP holds; most warrants are county-level.', confidence: 'high' }],
  UT: [{ label: 'Utah Statewide Warrants Search (BCI)', url: 'https://warrants.utah.gov/', description: 'Free statewide active warrant search by name.', confidence: 'high' }],
  VT: [{ label: 'Vermont Judiciary Public Portal', url: 'https://portal.vtcourts.gov/Portal/', description: 'Statewide case search; warrants issued at county level.', confidence: 'high' }],
  VA: [{ label: 'Virginia Online Case Information (OCIS)', url: 'https://eapps.courts.state.va.us/ocis/landing/false', description: 'Statewide criminal/traffic case search across courts.', confidence: 'high' }],
  WA: [{ label: 'Washington Courts Case Records Search', url: 'https://dw.courts.wa.gov/', description: 'Statewide case search, all court levels; warrants county-level.', confidence: 'high' }],
  WV: [{ label: 'West Virginia Court Record Access', url: 'https://www.courtswv.gov/court-record-access', description: 'Statewide magistrate and circuit court records, 55 counties.', confidence: 'high' }],
  WI: [{ label: 'Wisconsin Circuit Court Access (WCCA)', url: 'https://wcca.wicourts.gov/', description: 'Statewide circuit court case search; warrants county-level.', confidence: 'high' }],
  WY: [{ label: 'Wyoming Judicial Branch Case Search', url: 'https://efiling.courts.state.wy.us/public/caseSearch.do', description: 'Supreme Court docket search; trial/warrants at county level.', confidence: 'med' }],
};
