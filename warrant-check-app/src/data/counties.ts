import { Confidence, Jurisdiction, JurisdictionLevel, WarrantResource } from './types';

interface CountySeed {
  name: string;
  state: string;
  label: string;
  url: string;
  description: string;
  confidence: Confidence;
  /** Defaults to 'county'. Use 'city' for independent cities. */
  level?: JurisdictionLevel;
}

/**
 * Verified county/city-level warrant / court-record resources, compiled from
 * web research of official government sources. Coverage is population-weighted:
 * the largest states have several counties, and most other states have their
 * most-populous county. The US has 3,000+ counties, so this is intentionally
 * partial and grows over time.
 *
 * A county may appear on multiple lines when more than one official resource
 * exists (e.g. a statewide portal plus a county sheriff warrant search); they
 * are merged into one entry at build time.
 *
 * Last research pass: 2026-05.
 */
const COUNTY_SEEDS: CountySeed[] = [
  // California
  { name: 'Los Angeles County', state: 'CA', label: 'LASD Inmate Information Center', url: 'https://app5.lasd.org/iic', description: 'Sheriff inmate/warrant lookup, Los Angeles County.', confidence: 'med' },
  { name: 'San Diego County', state: 'CA', label: 'San Diego Sheriff Warrant Search', url: 'https://apps.sdsheriff.net/warrant/', description: 'Active San Diego Superior Court adult warrants.', confidence: 'high' },
  { name: 'Orange County', state: 'CA', label: 'OC Superior Court Case Search', url: 'https://visionpublic.occourts.org/Search.do', description: 'Orange County criminal/traffic case and warrant info.', confidence: 'high' },
  { name: 'Riverside County', state: 'CA', label: 'Riverside Superior Court Public Access', url: 'https://epublic-access.riverside.courts.ca.gov/', description: 'Criminal/civil case records search.', confidence: 'high' },
  { name: 'San Bernardino County', state: 'CA', label: 'San Bernardino Superior Court Portal', url: 'https://cap.sb-court.org/search', description: 'Criminal/civil case records, 1998–present.', confidence: 'high' },
  { name: 'Santa Clara County', state: 'CA', label: 'Santa Clara Superior Court Portal', url: 'https://portal.scscourt.org/search', description: 'Criminal, civil, and traffic case records.', confidence: 'high' },
  { name: 'Alameda County', state: 'CA', label: 'Alameda eCourt Public Portal', url: 'https://eportal.alameda.courts.ca.gov/', description: 'Civil, family, probate, and criminal records.', confidence: 'high' },
  { name: 'Sacramento County', state: 'CA', label: 'Sacramento Public Case Access', url: 'https://services.saccourt.ca.gov/PublicCaseAccess/', description: 'Criminal cases 1989–present, free.', confidence: 'high' },
  { name: 'Contra Costa County', state: 'CA', label: 'Contra Costa Smart Search Portal', url: 'https://kiosk-cacontracosta.tylertech.cloud/Portal/', description: 'Civil, criminal, and probate case records.', confidence: 'med' },
  { name: 'Fresno County', state: 'CA', label: 'Fresno Superior Court Public Portal', url: 'https://publicportal.fresno.courts.ca.gov/', description: 'Public criminal/civil case records.', confidence: 'high' },
  // Texas
  { name: 'Harris County', state: 'TX', label: 'Harris County Sheriff Warrant Search', url: 'https://harriscountyso.org/JailInfo/warrantssearch', description: 'Class A/B misdemeanor warrants, updated daily.', confidence: 'high' },
  { name: 'Dallas County', state: 'TX', label: 'Dallas County Wanted Search', url: 'https://www.dallascounty.org/dcwantedsearch/search.jsp', description: 'Official county active warrant/wanted search.', confidence: 'high' },
  { name: 'Tarrant County', state: 'TX', label: 'Tarrant County Sheriff Criminal Warrants', url: 'https://www.tarrantcountytx.gov/en/sheriff/operations-bureau/criminal-investigations/criminal-warrants.html', description: 'Criminal warrant info; limited public online release.', confidence: 'med' },
  { name: 'Bexar County', state: 'TX', label: 'Bexar County Justice Information Portal', url: 'https://www.bexar.org/3856/New-Justice-Information-Portal', description: 'Criminal case records, bonds, and warrants.', confidence: 'high' },
  { name: 'Travis County', state: 'TX', label: 'Travis County Odyssey Public Access', url: 'https://odysseypa.traviscountytx.gov/', description: 'District/county criminal and civil cases.', confidence: 'med' },
  { name: 'Collin County', state: 'TX', label: 'Collin County Courts Records Inquiry', url: 'https://cijspub.co.collin.tx.us/', description: 'Active warrants, criminal/civil cases.', confidence: 'high' },
  { name: 'Denton County', state: 'TX', label: 'Denton County Public Access', url: 'https://justice1.dentoncounty.gov/PublicAccess/', description: 'Criminal, civil, family, and probate records.', confidence: 'high' },
  { name: 'El Paso County', state: 'TX', label: 'El Paso County Case Records Search', url: 'https://portal-txelpaso.tylertech.cloud/PublicAccess/default.aspx', description: 'District/county criminal and civil cases.', confidence: 'high' },
  { name: 'Fort Bend County', state: 'TX', label: 'Fort Bend District Clerk Case Records', url: 'https://tylerpaw.fortbendcountytx.gov/PublicAccess/', description: 'Felony/misdemeanor district court records.', confidence: 'high' },
  { name: 'Hidalgo County', state: 'TX', label: 'Hidalgo County Odyssey Public Access', url: 'https://pa.co.hidalgo.tx.us/', description: 'District/county criminal, civil, and family.', confidence: 'high' },
  // Florida
  { name: 'Miami-Dade County', state: 'FL', label: 'Criminal Justice Online Case Search', url: 'https://www2.miamidadeclerk.gov/cjis/', description: 'Clerk criminal case search, Miami-Dade County.', confidence: 'high' },
  { name: 'Broward County', state: 'FL', label: 'Broward Clerk of Courts Case Search', url: 'https://www.browardclerk.org/web2', description: 'Public case/warrant search, Broward County.', confidence: 'high' },
  { name: 'Palm Beach County', state: 'FL', label: 'Palm Beach Clerk eCaseView', url: 'https://www.mypalmbeachclerk.com/records/court-records/court-records-search', description: 'Criminal/civil/traffic case search, Palm Beach County.', confidence: 'high' },
  { name: 'Hillsborough County', state: 'FL', label: 'Hillsborough Clerk HOVER Case Search', url: 'https://hover.hillsclerk.com/', description: 'Criminal and civil case records, free.', confidence: 'high' },
  { name: 'Orange County', state: 'FL', label: 'Orange County myeClerk Records Search', url: 'https://myeclerk.myorangeclerk.com/', description: 'Criminal and civil case records, free.', confidence: 'high' },
  { name: 'Pinellas County', state: 'FL', label: 'Pinellas Clerk Court Records Inquiry', url: 'https://courtrecords.mypinellasclerk.gov/', description: 'Criminal/civil case records, free.', confidence: 'high' },
  { name: 'Duval County', state: 'FL', label: 'Duval Clerk CORE ePortal', url: 'https://core.duvalclerk.com/CoreCms.aspx?mode=PublicAccess', description: 'Criminal/civil case records.', confidence: 'high' },
  { name: 'Lee County', state: 'FL', label: 'Lee County Clerk Records Inquiry', url: 'https://matrix.leeclerk.org/', description: 'Court case records and hearings, 2004–present.', confidence: 'high' },
  { name: 'Polk County', state: 'FL', label: 'Polk Records Online (PRO)', url: 'https://pro.polkcountyclerk.net/PRO', description: 'Civil/criminal case records and dockets.', confidence: 'high' },
  // Georgia
  { name: 'Fulton County', state: 'GA', label: 'Fulton County Sheriff', url: 'https://fcsoga.org/', description: 'In-person warrant check; sheriff is custodian.', confidence: 'med' },
  { name: 'Gwinnett County', state: 'GA', label: 'Gwinnett Sheriff JAIL View', url: 'https://www.gwinnettcountysheriff.org/', description: 'Inmate/warrant search, Gwinnett County.', confidence: 'med' },
  { name: 'Cobb County', state: 'GA', label: 'Cobb Magistrate Warrant Inquiry', url: 'https://www.cobbcounty.gov/magistrate-court/magistrate-court-warrant-division/warrant-inquiry', description: 'Active warrant inquiry, Cobb County.', confidence: 'high' },
  // Illinois
  { name: 'Cook County', state: 'IL', label: 'Cook County Clerk Case Search', url: 'https://casesearch.cookcountyclerkofcourt.org/', description: 'Circuit court case search, Cook County.', confidence: 'high' },
  { name: 'DuPage County', state: 'IL', label: 'DuPage Circuit Clerk Search', url: 'https://epay.dupagecircuitclerk.gov/Clerk/allsearch.do', description: 'Circuit court case search, DuPage County.', confidence: 'high' },
  { name: 'Lake County', state: 'IL', label: 'Lake County Circuit Clerk Access', url: 'https://circuitclerk.lakecountyil.gov/publicAccess/', description: 'Civil/criminal/traffic case search, Lake County.', confidence: 'high' },
  // New York (statewide WebCrims covers NYC and many counties)
  { name: 'Kings County (Brooklyn)', state: 'NY', label: 'NYS WebCrims Defendant Search', url: 'https://iapps.courts.state.ny.us/webcrim_attorney/DefendantSearch', description: 'Statewide portal covers Brooklyn; warrant status shown.', confidence: 'high' },
  { name: 'Queens County', state: 'NY', label: 'NYS WebCrims Defendant Search', url: 'https://iapps.courts.state.ny.us/webcrim_attorney/DefendantSearch', description: 'Statewide portal covers Queens; warrant status shown.', confidence: 'high' },
  { name: 'New York County (Manhattan)', state: 'NY', label: 'NYS WebCrims Defendant Search', url: 'https://iapps.courts.state.ny.us/webcrim_attorney/DefendantSearch', description: 'Statewide portal covers Manhattan; warrant status shown.', confidence: 'high' },
  { name: 'Bronx County', state: 'NY', label: 'NYS WebCrims Defendant Search', url: 'https://iapps.courts.state.ny.us/webcrim_attorney/DefendantSearch', description: 'Statewide portal covers the Bronx; no separate county tool.', confidence: 'high' },
  { name: 'Richmond County (Staten Island)', state: 'NY', label: 'NYS WebCrims Defendant Search', url: 'https://iapps.courts.state.ny.us/webcrim_attorney/DefendantSearch', description: 'Statewide portal covers Staten Island criminal cases.', confidence: 'high' },
  { name: 'Suffolk County', state: 'NY', label: 'NYS WebCrims Defendant Search', url: 'https://iapps.courts.state.ny.us/webcrim_attorney/DefendantSearch', description: 'Pending criminal cases by defendant name.', confidence: 'high' },
  { name: 'Nassau County', state: 'NY', label: 'NYS WebCrims Defendant Search', url: 'https://iapps.courts.state.ny.us/webcrim_attorney/DefendantSearch', description: 'Nassau District Court criminal cases.', confidence: 'high' },
  { name: 'Erie County', state: 'NY', label: 'Erie County Clerk BrowserView Search', url: 'https://ecclerk.erie.gov/BrowserView/', description: 'County clerk public records incl. warrant codes.', confidence: 'med' },
  { name: 'Monroe County', state: 'NY', label: 'Monroe County Clerk SearchIQS', url: 'https://searchiqs.com/nymonr/', description: 'County court/land records search.', confidence: 'med' },
  { name: 'Westchester County', state: 'NY', label: 'Westchester Records Online', url: 'https://wro.westchesterclerk.com/', description: 'Civil 1980+, criminal 1978–present.', confidence: 'high' },
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
  // Pennsylvania
  { name: 'Philadelphia County', state: 'PA', label: 'First Judicial District (Philadelphia)', url: 'https://www.courts.phila.gov/', description: 'County court system; use UJS portal for warrants.', confidence: 'med' },
  { name: 'Allegheny County', state: 'PA', label: 'Allegheny County Criminal Records', url: 'https://www.alleghenycounty.us/Government/Court-Related/Criminal-Records', description: 'County criminal records; UJS portal for dockets.', confidence: 'high' },
  { name: 'Montgomery County', state: 'PA', label: 'PA UJS Web Portal', url: 'https://ujsportal.pacourts.us/casesearch', description: 'Statewide portal covers Montgomery County dockets.', confidence: 'med' },
  // Ohio
  { name: 'Franklin County', state: 'OH', label: 'Franklin County Clerk Case Info Online', url: 'https://fcdcfcjs.co.franklin.oh.us/CaseInformationOnline/', description: 'Common Pleas case search; municipal shows warrant status.', confidence: 'high' },
  { name: 'Cuyahoga County', state: 'OH', label: 'Cuyahoga Clerk of Courts Docket', url: 'https://cpdocket.cp.cuyahogacounty.gov/Search.aspx', description: 'Common Pleas case and docket records search.', confidence: 'high' },
  { name: 'Hamilton County', state: 'OH', label: 'Hamilton County Clerk Records Search', url: 'https://www.courtclerk.org/records-search/', description: 'County court records search: civil/criminal/traffic.', confidence: 'high' },
  // Arizona
  { name: 'Maricopa County', state: 'AZ', label: 'Maricopa Superior Court Docket', url: 'https://www.superiorcourt.maricopa.gov/docket/index.asp', description: 'Superior court criminal/civil case dockets.', confidence: 'high' },
  { name: 'Pima County', state: 'AZ', label: 'Pima County Justice Court Case Search', url: 'https://www.jp.pima.gov/CaseSearch/', description: 'Justice court civil/criminal/traffic cases.', confidence: 'high' },
  // Washington
  { name: 'King County', state: 'WA', label: 'King County Superior Court Clerk Search', url: 'https://dja-prd-ecexap1.kingcounty.gov/node/501', description: 'Superior court case records incl. warrants.', confidence: 'high' },
  { name: 'Pierce County', state: 'WA', label: 'Pierce County LINX Online Records', url: 'https://linxonline.co.pierce.wa.us/linxweb/Search.cfm', description: 'County court records incl. warrant info.', confidence: 'high' },
  // Massachusetts
  { name: 'Middlesex County', state: 'MA', label: 'MA Trial Court Electronic Case Access', url: 'https://www.mass.gov/search-court-dockets-calendars-and-case-information', description: 'Statewide trial court dockets/case info.', confidence: 'high' },
  { name: 'Suffolk County', state: 'MA', label: 'MA Trial Court Electronic Case Access', url: 'https://www.mass.gov/search-court-dockets-calendars-and-case-information', description: 'Statewide trial court dockets/case info.', confidence: 'high' },
  // Virginia
  { name: 'Fairfax County', state: 'VA', label: 'Fairfax Circuit Court eCaseSearch', url: 'https://www.fairfaxcounty.gov/apps/ECS_Public/', description: 'Circuit court criminal/civil cases.', confidence: 'high' },
  { name: 'Virginia Beach', state: 'VA', level: 'city', label: 'Virginia Online Case Information', url: 'https://www.vacourts.gov/caseinfo/home', description: 'Circuit/district court cases by locality.', confidence: 'high' },
  // Maryland
  { name: 'Montgomery County', state: 'MD', label: 'Maryland Judiciary Case Search', url: 'https://casesearch.courts.state.md.us/casesearch', description: 'Statewide district/circuit court records.', confidence: 'high' },
  { name: "Prince George's County", state: 'MD', label: 'Maryland Judiciary Case Search', url: 'https://casesearch.courts.state.md.us/casesearch', description: 'Statewide district/circuit court records.', confidence: 'high' },
  // Colorado
  { name: 'Denver County', state: 'CO', label: 'Denver County Court Public Portal', url: 'https://public.denvercountycourt.org/Case/Quick', description: 'County court case + warrant address search.', confidence: 'high' },
  { name: 'El Paso County', state: 'CO', label: 'Colorado Judicial Courts Records Search', url: 'https://www.coloradojudicial.gov/courts-records-search', description: 'Statewide court docket/records search.', confidence: 'med' },
  // Minnesota
  { name: 'Hennepin County', state: 'MN', label: 'Minnesota Court Records Online (MCRO)', url: 'https://publicaccess.courts.state.mn.us/CaseSearch', description: 'Statewide court case search incl. warrants.', confidence: 'high' },
  { name: 'Ramsey County', state: 'MN', label: 'Ramsey Sheriff Online Warrant Search', url: 'https://www.ramseycountymn.gov/your-government/leadership/sheriffs-office/sheriffs-office-divisions/administration/arrest-warrant-search', description: 'Active arrest warrants, 2nd Judicial District.', confidence: 'high' },
  // Wisconsin
  { name: 'Milwaukee County', state: 'WI', label: 'Wisconsin Circuit Court Access (WCCA)', url: 'https://wcca.wicourts.gov/', description: 'Statewide circuit court cases/warrants.', confidence: 'high' },
  { name: 'Dane County', state: 'WI', label: 'Wisconsin Circuit Court Access (WCCA)', url: 'https://wcca.wicourts.gov/', description: 'Statewide circuit court cases/warrants.', confidence: 'high' },
  // Indiana
  { name: 'Marion County', state: 'IN', label: 'Indiana MyCase Court Search', url: 'https://public.courts.in.gov/mycase', description: 'Statewide court records, docket warrant entries.', confidence: 'med' },
  { name: 'Lake County', state: 'IN', label: 'Lake County Sheriff Warrants', url: 'https://lakecountysheriff.com/page.php?id=62', description: 'Sheriff active/outstanding warrants.', confidence: 'med' },
  // Missouri
  { name: 'St. Louis County', state: 'MO', label: 'Missouri Case.net', url: 'https://www.courts.mo.gov/casenet/welcome.do', description: 'Statewide court case records/charges.', confidence: 'high' },
  { name: 'Jackson County', state: 'MO', label: 'Missouri Case.net (16th Circuit)', url: 'https://www.courts.mo.gov/casenet/welcome.do', description: 'Statewide court case records/charges.', confidence: 'high' },
  // Tennessee
  { name: 'Shelby County', state: 'TN', label: 'Shelby County Criminal Justice Portal', url: 'https://cjs.shelbycountytn.gov/CJS/', description: 'County criminal court records (registration).', confidence: 'high' },
  { name: 'Davidson County', state: 'TN', label: 'Davidson Criminal Court Clerk Search', url: 'https://sci.ccc.nashville.gov/', description: 'Criminal court case info, charges/disposition.', confidence: 'high' },
  // Oregon
  { name: 'Multnomah County', state: 'OR', label: 'Oregon eCourt Records Search (OECI)', url: 'https://webportal.courts.oregon.gov/portal/', description: 'Statewide circuit court case search.', confidence: 'high' },
  { name: 'Washington County', state: 'OR', label: 'Oregon eCourt Records Search (OECI)', url: 'https://webportal.courts.oregon.gov/portal/', description: 'Statewide circuit court case search.', confidence: 'high' },
  // Nevada
  { name: 'Clark County', state: 'NV', label: 'Clark County Justice Court Case Search', url: 'https://cvpublicaccess.clarkcountynv.gov/', description: 'Justice court criminal/civil/traffic cases.', confidence: 'high' },
  { name: 'Washoe County', state: 'NV', label: 'Washoe Second Judicial District Court', url: 'https://www.washoecourts.com/', description: 'District court case & calendar inquiry.', confidence: 'high' },
  // Utah
  { name: 'Salt Lake County', state: 'UT', label: 'Utah Statewide Warrants Search (DPS)', url: 'https://warrants.utah.gov/', description: 'Statewide active warrants, all counties.', confidence: 'high' },
  { name: 'Utah County', state: 'UT', label: 'Utah Courts XChange Case Search', url: 'https://www.utcourts.gov/en/court-records-publications/records/xchange.html', description: 'Statewide district/justice court case records.', confidence: 'high' },
  // Oklahoma
  { name: 'Oklahoma County', state: 'OK', label: 'OSCN Docket Search', url: 'https://www.oscn.net/dockets/Search.aspx', description: 'Statewide court dockets incl. Oklahoma County.', confidence: 'high' },
  { name: 'Oklahoma County', state: 'OK', label: 'Oklahoma County Sheriff Warrant Search', url: 'https://docs.oklahomacounty.org/sheriff/warrantsearch/', description: 'County active warrants by name.', confidence: 'high' },
  { name: 'Tulsa County', state: 'OK', label: 'Tulsa County District Court Clerk Records', url: 'https://courtclerk.tulsacounty.org/Home/Records', description: 'County court records/archives (also via OSCN).', confidence: 'high' },
  // Kentucky
  { name: 'Jefferson County', state: 'KY', label: 'Kentucky CourtNet 2.0 Case Search', url: 'https://kcoj.kycourts.net/CourtNet/Search/', description: 'Statewide case search; select Jefferson.', confidence: 'high' },
  { name: 'Fayette County', state: 'KY', label: 'Kentucky CourtNet 2.0 Case Search', url: 'https://kcoj.kycourts.net/CourtNet/Search/', description: 'Statewide case search; select Fayette.', confidence: 'high' },
  // South Carolina
  { name: 'Greenville County', state: 'SC', label: 'Greenville County Public Index', url: 'https://www2.greenvillecounty.org/scjd/publicindex', description: 'County court case public index.', confidence: 'high' },
  { name: 'Charleston County', state: 'SC', label: 'Charleston County Public Index', url: 'https://jcmsweb.charlestoncounty.org/publicindex/', description: 'County court case public index.', confidence: 'high' },
  // Alabama
  { name: 'Jefferson County', state: 'AL', label: 'Alabama Trial Court Records (Alacourt)', url: 'https://pa.alacourt.com/', description: 'Statewide case records by name/case number.', confidence: 'high' },
  { name: 'Mobile County', state: 'AL', label: 'Mobile County 13th Judicial Circuit', url: 'https://mobile.alacourt.gov/', description: 'County court info; records via pa.alacourt.com.', confidence: 'med' },
  // Louisiana
  { name: 'East Baton Rouge Parish', state: 'LA', label: 'EBR Clerk of Court (Clerk Connect)', url: 'https://clerkconnect.com/courtEventInquiry/ebr', description: 'Parish civil/criminal/traffic case search.', confidence: 'high' },
  { name: 'Orleans Parish', state: 'LA', label: 'Orleans Criminal District Court Docket Master', url: 'https://www.opso.us/dcktmstr/dcktmstr.php', description: 'Parish criminal court docket search.', confidence: 'high' },
  // Kansas
  { name: 'Johnson County', state: 'KS', label: 'Kansas Courts Case Search', url: 'https://casesearch.kscourts.gov/', description: 'Statewide case search incl. Johnson.', confidence: 'high' },
  { name: 'Johnson County', state: 'KS', label: 'Johnson County Sheriff Warrant Unit', url: 'https://www.jocogov.org/johnson-county-sheriff/operations-bureau/warrant-unit', description: 'County warrant unit info/lookup.', confidence: 'med' },
  { name: 'Sedgwick County', state: 'KS', label: 'Sedgwick County Sheriff Warrant Search', url: 'https://ssc.sedgwickcounty.org/warrantsearch/', description: 'County active warrant search by name.', confidence: 'high' },
  // Iowa
  { name: 'Polk County', state: 'IA', label: 'Iowa Courts Online Search', url: 'https://www.iowacourts.state.ia.us/', description: 'Statewide case/docket search incl. Polk.', confidence: 'high' },
  // Arkansas
  { name: 'Pulaski County', state: 'AR', label: 'Arkansas CourtConnect (Search ARCourts)', url: 'https://caseinfo.arcourts.gov/opad', description: 'Statewide case search incl. Pulaski.', confidence: 'high' },
  // New Mexico
  { name: 'Bernalillo County', state: 'NM', label: 'New Mexico Courts Case Lookup', url: 'https://caselookup.nmcourts.gov/caselookup/app', description: 'Statewide district/magistrate/metro cases.', confidence: 'high' },
  // Nebraska
  { name: 'Douglas County', state: 'NE', label: 'Nebraska JUSTICE Case Search', url: 'https://www.nebraska.gov/justicecc/ccname.cgi', description: 'Statewide trial court case search (paid).', confidence: 'high' },
  // Idaho
  { name: 'Ada County', state: 'ID', label: 'Idaho iCourt Portal Smart Search', url: 'https://mycourts.idaho.gov/', description: 'Statewide court records; select Ada County.', confidence: 'high' },
  // Connecticut (no county government)
  { name: 'Hartford County', state: 'CT', label: 'CT Judicial Criminal/MV Case Look-up', url: 'https://www.jud.ct.gov/crim.htm', description: 'Statewide criminal/MV case lookup (no county govt).', confidence: 'high' },
  { name: 'New Haven County', state: 'CT', label: 'CT Judicial Criminal/MV Case Look-up', url: 'https://www.jud.ct.gov/crim.htm', description: 'Statewide; shows bench warrants by name (no county govt).', confidence: 'high' },
  { name: 'Fairfield County', state: 'CT', label: 'CT Judicial Criminal/MV Case Look-up', url: 'https://www.jud.ct.gov/crim.htm', description: 'Statewide; shows bench warrants by name (no county govt).', confidence: 'high' },

  // --- Second/third counties & remaining states (2026-05 research pass 2) ---

  // Georgia
  { name: 'DeKalb County', state: 'GA', label: 'DeKalb County Active Warrant Search', url: 'https://spatial.dekalbcounty.org/warrants/welcome.asp', description: 'Active arrest warrants, updated nightly.', confidence: 'high' },
  { name: 'Chatham County', state: 'GA', label: 'Chatham Superior Court Criminal Division', url: 'https://superiorcourtclerk.chathamcountyga.gov/Superior/CriminalDivision', description: 'Superior court criminal records, Savannah.', confidence: 'med' },
  // Illinois
  { name: 'Will County', state: 'IL', label: 'Will County Circuit Clerk Case Lookup', url: 'https://www.circuitclerkofwillcounty.com/Public-Access/Case-Lookup', description: 'Circuit court civil/criminal/traffic cases.', confidence: 'high' },
  { name: 'Kane County', state: 'IL', label: 'Kane County Sheriff Warrants Division', url: 'https://www.kanesheriff.com/Pages/Civil-Warrants-Division.aspx', description: 'Warrant division info; no online search.', confidence: 'med' },
  // Michigan
  { name: 'Kent County', state: 'MI', label: 'Kent County Sheriff', url: 'https://www.accesskent.com/Sheriff/', description: 'Sheriff warrant lookup via Warrants tab.', confidence: 'med' },
  { name: 'Genesee County', state: 'MI', label: 'Michigan MiCOURT Case Search', url: 'https://micourt.courts.michigan.gov/case-search/', description: 'Statewide court case search; select Genesee.', confidence: 'high' },
  // New Jersey
  { name: 'Hudson County', state: 'NJ', label: 'NJ Courts Criminal Public Access', url: 'https://portal.njcourts.gov/webe41/ExternalPGPA/CaptchaServlet', description: 'Statewide criminal search covers Hudson.', confidence: 'med' },
  { name: 'Monmouth County', state: 'NJ', label: 'Monmouth County Sheriff Warrants', url: 'https://www.mcsonj.org/divisions/law-enforcement/200-2-2/', description: 'Active arrest warrant/fugitive section.', confidence: 'med' },
  { name: 'Ocean County', state: 'NJ', label: 'Ocean County Sheriff', url: 'https://sheriff.co.ocean.nj.us/', description: 'Sheriff warrants; phone/in-person only.', confidence: 'med' },
  // North Carolina
  { name: 'Forsyth County', state: 'NC', label: 'Forsyth Sheriff P2C Wanted List', url: 'https://p2c.fcso.us/wantedlist.aspx', description: 'Online active arrest warrant/wanted list.', confidence: 'high' },
  { name: 'Durham County', state: 'NC', label: 'NC eCourts Portal', url: 'https://portal.nccourts.gov/', description: 'Statewide portal covers Durham County.', confidence: 'high' },
  // Pennsylvania
  { name: 'Bucks County', state: 'PA', label: 'Bucks County Web Viewer Case Search', url: 'https://propublic.buckscountyonline.org/PSI/v/search/case', description: 'Civil/criminal/family court case search.', confidence: 'high' },
  { name: 'Delaware County', state: 'PA', label: 'Delaware County Public Access Web Viewer', url: 'https://delcopublicaccess.co.delaware.pa.us/', description: 'Common Pleas civil/criminal dockets.', confidence: 'high' },
  { name: 'Chester County', state: 'PA', label: 'PA UJS Web Portal', url: 'https://ujsportal.pacourts.us/casesearch', description: 'Statewide portal covers Chester County dockets.', confidence: 'med' },
  // Ohio
  { name: 'Summit County', state: 'OH', label: 'Summit County Clerk of Courts Search', url: 'https://clerkweb.summitoh.net/', description: 'Civil/criminal/domestic/appeals case search.', confidence: 'high' },
  { name: 'Montgomery County', state: 'OH', label: 'Montgomery County Clerk PRO System', url: 'https://pro.mcohio.org/', description: 'Traffic/criminal/civil case records search.', confidence: 'high' },
  { name: 'Lucas County', state: 'OH', label: 'Lucas County Clerk Online Dockets', url: 'https://www.co.lucas.oh.us/3707/Online-Dockets', description: 'Common Pleas case info/dockets.', confidence: 'high' },
  // Arizona
  { name: 'Pinal County', state: 'AZ', label: 'Pinal County Superior Court — Criminal', url: 'https://www.pinalcourtsaz.gov/196/Criminal-Cases', description: 'Superior court criminal cases; statewide lookup.', confidence: 'med' },
  // Washington
  { name: 'Snohomish County', state: 'WA', label: 'Snohomish County Access Court Records', url: 'https://www.snohomishcountywa.gov/5508/Access-Court-Records', description: 'Superior court records access info.', confidence: 'med' },
  { name: 'Spokane County', state: 'WA', label: 'Spokane County Court Document Viewer', url: 'https://cp.spokanecounty.org/courtdocumentviewer/', description: 'Superior/district court case + warrant search.', confidence: 'high' },
  // Massachusetts
  { name: 'Worcester County', state: 'MA', label: 'MassCourts eAccess', url: 'https://www.masscourts.org/eservices/home.page', description: 'Statewide trial court dockets; select Worcester.', confidence: 'med' },
  { name: 'Essex County', state: 'MA', label: 'MassCourts eAccess', url: 'https://www.masscourts.org/eservices/home.page', description: 'Statewide trial court dockets; select Essex.', confidence: 'med' },
  // Virginia
  { name: 'Prince William County', state: 'VA', label: 'Virginia Online Case Information', url: 'https://eapps.courts.state.va.us/ocis/landing/false', description: 'Statewide criminal/traffic case search.', confidence: 'high' },
  { name: 'Loudoun County', state: 'VA', label: 'Virginia Online Case Information', url: 'https://eapps.courts.state.va.us/ocis/landing/false', description: 'Statewide criminal/traffic case search.', confidence: 'high' },
  // Maryland
  { name: 'Baltimore County', state: 'MD', label: 'Maryland Judiciary Case Search', url: 'https://casesearch.courts.state.md.us/casesearch', description: 'Circuit/district court records; select county.', confidence: 'high' },
  { name: 'Anne Arundel County', state: 'MD', label: 'Maryland Judiciary Case Search', url: 'https://casesearch.courts.state.md.us/casesearch', description: 'Circuit/district court records; select county.', confidence: 'high' },
  // Colorado
  { name: 'Arapahoe County', state: 'CO', label: 'Colorado Judicial Docket Search', url: 'https://www.courts.state.co.us/Courts/County/Dockets.cfm?County_ID=57', description: 'Statewide judicial dockets; Arapahoe selectable.', confidence: 'high' },
  { name: 'Jefferson County', state: 'CO', label: 'Jefferson County Sheriff Warrant Search', url: 'https://www.jeffco.us/615/Warrants', description: 'Searchable active warrants by name.', confidence: 'high' },
  // Minnesota
  { name: 'Dakota County', state: 'MN', label: 'Dakota County Sheriff Warrant Search', url: 'https://www.co.dakota.mn.us/LawJustice/Warrants/Search/Pages/default.aspx', description: 'Active warrant search by name/DOB.', confidence: 'high' },
  { name: 'Anoka County', state: 'MN', label: 'Anoka County Online Warrant Search', url: 'https://www.anokacountymn.gov/3266/Online-Warrant-Search', description: 'Active district court warrants searchable.', confidence: 'high' },
  // Wisconsin
  { name: 'Waukesha County', state: 'WI', label: 'Wisconsin Circuit Court Access (WCCA)', url: 'https://wcca.wicourts.gov/', description: 'Statewide case search; Waukesha selectable.', confidence: 'high' },
  { name: 'Brown County', state: 'WI', label: 'Brown County Sheriff Outstanding Warrants', url: 'https://www.brownso.org/warrants/', description: 'Outstanding warrant list by name.', confidence: 'high' },
  // Indiana
  { name: 'Allen County', state: 'IN', label: 'Indiana MyCase Court Search', url: 'https://public.courts.in.gov/mycase/', description: 'Statewide case search; sheriff has no online tool.', confidence: 'high' },
  { name: 'Hamilton County', state: 'IN', label: 'Hamilton County Sheriff Open Warrants', url: 'https://www.hcsheriff.gov/cid/owio/', description: 'Open warrants by name/number/address (verify vs TN).', confidence: 'med' },
  // Missouri
  { name: 'St. Charles County', state: 'MO', label: 'Missouri Case.net', url: 'https://www.courts.mo.gov/casenet/base/welcome.do', description: 'Statewide case search; St. Charles selectable.', confidence: 'high' },
  { name: 'Greene County', state: 'MO', label: 'Greene County Sheriff Warrants Division', url: 'https://greenecountymo.gov/sheriff/division/warrants_div.php', description: 'Warrants division info; also Case.net.', confidence: 'med' },
  // Tennessee
  { name: 'Knox County', state: 'TN', label: 'Knox County Sheriff Criminal Warrants', url: 'https://knoxsheriff.org/warrants/', description: 'Active criminal warrant info/search.', confidence: 'high' },
  { name: 'Hamilton County', state: 'TN', label: 'Hamilton County Sheriff Open Warrants', url: 'https://www.hcsheriff.gov/', description: 'Open warrants searchable (verify domain vs IN).', confidence: 'med' },
  { name: 'Rutherford County', state: 'TN', label: 'Rutherford County Sheriff Warrants', url: 'https://rcsotn.com/warrants-division', description: 'Warrant verification; no public online DB.', confidence: 'med' },
  // Oregon
  { name: 'Clackamas County', state: 'OR', label: 'Oregon eCourt Records (OECI)', url: 'https://www.courts.oregon.gov/courts/clackamas/records/pages/default.aspx', description: 'Statewide court records; Clackamas selectable.', confidence: 'high' },
  { name: 'Lane County', state: 'OR', label: 'Oregon eCourt Records (OECI)', url: 'https://www.courts.oregon.gov/courts/lane/', description: 'Statewide OECI records; sheriff in-person only.', confidence: 'med' },
  // Utah
  { name: 'Davis County', state: 'UT', label: 'Utah Statewide Warrants Search (BCI)', url: 'https://bci.utah.gov/warrants/', description: 'Free statewide active warrant search.', confidence: 'med' },
  { name: 'Weber County', state: 'UT', label: 'Utah Statewide Warrants Search (BCI)', url: 'https://bci.utah.gov/warrants/', description: 'Free statewide active warrant search.', confidence: 'med' },
  // Oklahoma
  { name: 'Cleveland County', state: 'OK', label: 'Cleveland County Sheriff Records', url: 'https://www.ccso-ok.us/233/Records', description: 'Warrant records; also OSCN court search.', confidence: 'med' },
  // Kentucky
  { name: 'Kenton County', state: 'KY', label: 'Kenton County Sheriff Current Warrants', url: 'https://www.kentoncountysheriff.org/205/Current-Warrants', description: 'Current warrant list.', confidence: 'high' },
  { name: 'Boone County', state: 'KY', label: 'Kentucky Court of Justice — Boone County', url: 'https://www.kycourts.gov/Courts/County-Information/Pages/Boone.aspx', description: 'Court info; CourtNet statewide records.', confidence: 'med' },
  // South Carolina
  { name: 'Richland County', state: 'SC', label: 'SC Judicial Public Index — Richland', url: 'https://publicindex.sccourts.org/Richland/PublicIndex/PISearch.aspx', description: 'County case records search.', confidence: 'high' },
  { name: 'Horry County', state: 'SC', label: 'SC Judicial Public Index — Horry', url: 'https://publicindex.sccourts.org/horry/publicindex/', description: 'County case records search.', confidence: 'high' },
  // Alabama
  { name: 'Madison County', state: 'AL', label: 'Madison Circuit Court Magistrate Warrants', url: 'https://madison.alacourt.gov/magistrates-warrants/', description: 'County circuit court warrant list page.', confidence: 'high' },
  { name: 'Baldwin County', state: 'AL', label: 'Baldwin County Sheriff Warrants Division', url: 'https://sheriff.baldwincountyal.gov/divisions-details/warrantdetails', description: 'Sheriff warrant info; no public name search.', confidence: 'med' },
  // Louisiana
  { name: 'Jefferson Parish', state: 'LA', label: 'Jefferson Parish Sheriff (JPSO)', url: 'https://www.jpso.com/', description: 'Sheriff site; warrant search under public services.', confidence: 'med' },
  { name: 'St. Tammany Parish', state: 'LA', label: 'St. Tammany Clerk Criminal Records', url: 'https://www.sttammanyclerk.org/departments/criminal/', description: 'Clerk criminal case records for the parish.', confidence: 'med' },
  // Kansas
  { name: 'Shawnee County', state: 'KS', label: 'Kansas District Court Case Search', url: 'https://casesearch.kscourts.gov/', description: 'Statewide portal; Shawnee selectable.', confidence: 'high' },
  { name: 'Wyandotte County', state: 'KS', label: 'Kansas District Court Case Search', url: 'https://casesearch.kscourts.gov/', description: 'Statewide portal; Wyandotte selectable.', confidence: 'high' },
  // Iowa
  { name: 'Linn County', state: 'IA', label: 'Linn County Sheriff', url: 'https://www.linncountyiowa.gov/151/Sheriffs-Office', description: 'Sheriff office; also Iowa Courts Online.', confidence: 'med' },
  { name: 'Scott County', state: 'IA', label: 'Scott County Sheriff Warrant Search', url: 'https://www.scottcountyiowa.gov/sheriff/warrants', description: 'Searchable active sheriff warrant database.', confidence: 'high' },
  // Arkansas
  { name: 'Benton County', state: 'AR', label: 'Benton County Sheriff Warrant Search', url: 'https://sheriff.bentoncountyar.gov/WarrantSearch/WarrantData.aspx', description: 'Searchable sheriff active warrant database.', confidence: 'high' },
  { name: 'Washington County', state: 'AR', label: 'Washington County Sheriff Warrants', url: 'https://www.washcosoar.gov/res/Warrants.aspx', description: 'Searchable sheriff active warrant database.', confidence: 'high' },
  // New Mexico
  { name: 'Doña Ana County', state: 'NM', label: 'New Mexico Courts Case Lookup', url: 'https://caselookup.nmcourts.gov/caselookup/', description: 'Statewide portal; Doña Ana cases searchable.', confidence: 'high' },
  { name: 'Santa Fe County', state: 'NM', label: 'New Mexico Courts Case Lookup', url: 'https://caselookup.nmcourts.gov/caselookup/', description: 'Statewide portal; Santa Fe cases searchable.', confidence: 'high' },
  // Nebraska
  { name: 'Lancaster County', state: 'NE', label: 'Lancaster County Sheriff Warrant List', url: 'https://www.lancaster.ne.gov/450/Warrant-List', description: 'County sheriff active warrant list.', confidence: 'high' },
  { name: 'Sarpy County', state: 'NE', label: 'Sarpy County Sheriff Active Warrants', url: 'https://apps.sarpy.gov/warrants/', description: 'Searchable sheriff active warrant database.', confidence: 'high' },
  // Idaho
  { name: 'Canyon County', state: 'ID', label: 'Canyon County Sheriff Warrants Unit', url: 'https://www.canyoncounty.id.gov/elected-officials/sheriff/warrants-unit/', description: 'Sheriff warrant info page.', confidence: 'med' },
  { name: 'Kootenai County', state: 'ID', label: 'Kootenai County Sheriff Active Warrants', url: 'https://www.kcsheriff.com/168/Active-Warrants', description: 'Sheriff active warrant lookup page.', confidence: 'high' },
  // Nevada
  { name: 'Carson City', state: 'NV', level: 'city', label: 'Carson City Sheriff Active Warrants', url: 'https://www.carsoncity.gov/government/departments-g-z/sheriff-s-office/crime-in-our-area', description: 'Sheriff active warrant report page.', confidence: 'med' },
  // Alaska
  { name: 'Anchorage Municipality', state: 'AK', label: 'Alaska CourtView (statewide)', url: 'https://records.courts.alaska.gov/eaccess/home.page.2', description: 'Statewide trial court cases; search by name/case.', confidence: 'high' },
  // Delaware
  { name: 'New Castle County', state: 'DE', label: 'Delaware Courts CourtConnect', url: 'https://courts.delaware.gov/docket.aspx', description: 'Statewide civil/criminal dockets; New Castle included.', confidence: 'med' },
  // Hawaii
  { name: 'Honolulu County', state: 'HI', label: 'Hawaii eCourt Kokua', url: 'https://www.courts.state.hi.us/legal_references/records/jims_system_availability', description: 'Statewide criminal/civil/traffic case info.', confidence: 'med' },
  // Maine
  { name: 'Cumberland County', state: 'ME', label: 'Maine re:SearchMaine eCourts', url: 'https://www.courts.maine.gov/ecourts/', description: 'Statewide e-filed cases; phased county rollout.', confidence: 'med' },
  // Mississippi
  { name: 'Hinds County', state: 'MS', label: 'Hinds County Sheriff', url: 'https://www.hindscountyms.com/elected-offices/sheriff', description: 'County sheriff warrant/arrest info.', confidence: 'med' },
  { name: 'Harrison County', state: 'MS', label: 'Harrison County Sheriff', url: 'https://www.harrisoncountysheriff.com/', description: 'County sheriff inmate/warrant info.', confidence: 'med' },
  // Montana
  { name: 'Yellowstone County', state: 'MT', label: 'Yellowstone Justice Court Record Search', url: 'https://www.yellowstonecountymt.gov/justicecourt/JCRecordSearch.asp', description: 'County justice court case records.', confidence: 'high' },
  // North Dakota
  { name: 'Cass County', state: 'ND', label: 'North Dakota Courts Public Search', url: 'https://publicsearch.ndcourts.gov/', description: 'Statewide criminal/civil/traffic cases; select Cass.', confidence: 'high' },
  // New Hampshire
  { name: 'Hillsborough County', state: 'NH', label: 'NH Judiciary Case Access Portal', url: 'https://odypa.nhecourt.us/portal', description: 'Statewide e-filed case records.', confidence: 'med' },
  // Rhode Island
  { name: 'Providence County', state: 'RI', label: 'RI Judiciary Public Portal Smart Search', url: 'https://publicportal.courts.ri.gov/PublicPortal/', description: 'Statewide cases incl. bench warrants.', confidence: 'high' },
  // South Dakota
  { name: 'Minnehaha County', state: 'SD', label: 'South Dakota UJS Records Search', url: 'https://ujs.sd.gov/cases-and-records/court-records-search/', description: 'Statewide court records; eCourts/PARS access.', confidence: 'med' },
  // Vermont
  { name: 'Chittenden County', state: 'VT', label: 'Vermont Judiciary Public Portal', url: 'https://portal.vtcourts.gov/Portal/Home/Dashboard/29', description: 'Statewide case/hearing records.', confidence: 'high' },
  // West Virginia
  { name: 'Kanawha County', state: 'WV', label: 'Kanawha County Sheriff Warrants', url: 'https://www.kanawhasheriff.us/law-enforcement/warrants/', description: 'County sheriff active warrants by patrol area.', confidence: 'high' },
  { name: 'Berkeley County', state: 'WV', label: 'WV Magistrate Case Record Search', url: 'https://mcrsearch.courtswv.gov/', description: 'Statewide magistrate court records, all 55 counties.', confidence: 'high' },
  // Wyoming
  { name: 'Laramie County', state: 'WY', label: 'Wyoming Judicial Branch Case Search', url: 'https://efiling.courts.state.wy.us/public/caseSearch.do', description: 'Statewide court case search.', confidence: 'med' },
  { name: 'Natrona County', state: 'WY', label: 'Natrona County Sheriff Warrants Search', url: 'https://warrants.natronacounty-wy.gov/', description: 'County warrants, 7th Judicial District/Circuit.', confidence: 'high' },
];

function slug(s: string): string {
  return s.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
}

function seedId(c: CountySeed): string {
  return `county-${c.state.toLowerCase()}-${slug(c.name)}`;
}

/** Group seeds by jurisdiction id, merging multiple resources into one entry. */
export const SEED_COUNTIES: Jurisdiction[] = (() => {
  const byId = new Map<string, Jurisdiction>();
  for (const c of COUNTY_SEEDS) {
    const id = seedId(c);
    const resource: WarrantResource = {
      label: c.label,
      url: c.url,
      description: c.description,
      confidence: c.confidence,
    };
    const existing = byId.get(id);
    if (existing) {
      existing.resources.push(resource);
      existing.verified = existing.verified || c.confidence === 'high';
    } else {
      byId.set(id, {
        id,
        name: c.name,
        level: c.level ?? 'county',
        state: c.state,
        parentId: `state-${c.state.toLowerCase()}`,
        resources: [resource],
        verified: c.confidence === 'high',
      });
    }
  }
  return Array.from(byId.values());
})();
