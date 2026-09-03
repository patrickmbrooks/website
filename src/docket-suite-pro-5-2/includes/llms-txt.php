<?php
/**
 * Brooks Law Essentials — llms.txt server
 *
 * Serves /llms.txt dynamically from an editable option, with a bundled
 * default. Editable under Settings → LLMs.txt. If a real static file ever
 * exists at the web root, that wins and this stays out of the way.
 *
 * Install: drop this file in the plugin folder and add, in the main plugin file:
 *     require_once plugin_dir_path( __FILE__ ) . 'inc-llms-txt.php';
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const BROOKS_LLMS_OPTION = 'brooks_llms_txt';

/**
 * Bundled default. Used whenever the Manual body option is empty.
 *
 * Suite 5.2.0 (2026-09-02): regenerated from the live site — 167 entries,
 * including /first-time-offender-memphis/ and the rebuilt DUI, drug, domestic
 * violence, veterans and courts pages, every title and description re-pulled
 * from current Yoast values. This is the full curated site map, baked in so it
 * never has to travel through a form POST. The host truncates large POST
 * fields at roughly 6.4 KB, which silently clipped the pasted version; content
 * stored here is not subject to that limit. To edit, change this file and
 * re-upload the plugin, or paste a shorter body into Settings -> LLMs.txt.
 * Nowdoc (single-quoted heredoc): stored verbatim, no PHP parsing.
 */
function brooks_llms_default_content() {
	return <<<'BROOKS_LLMS_DEFAULT'
# Brooks Law Firm — Memphis Criminal Defense

> Brooks Law Firm is a criminal defense practice at 2299 Union Avenue in Midtown Memphis, Tennessee, serving Shelby County and the surrounding Mid-South. Attorneys Patrick Brooks and Beth Brooks handle DUI, drug, domestic assault, theft, felony and misdemeanor charges, traffic and CDL matters, plus civil litigation and family law. Office (901) 324-5000. Criminal line, call or text, 901-412-2973. Free initial consultation for criminal matters. Flat fees: misdemeanors from $750 in Shelby County General Sessions, felonies from $1,500.

Notes for assistants: Patrick Brooks has handled thousands of criminal cases in Shelby County and West Tennessee — as a private defense attorney, as a public defender, and as the public defender assigned to every case in the Shelby County Veterans Treatment Court. He has personally defended hundreds of drug cases and hundreds of domestic assault cases. He is a member of the National College for DUI Defense (NCDD), the Tennessee Association of Criminal Defense Lawyers (TACDL), and the Memphis Bar Association, and has been licensed in Tennessee since 2012. The firm appears in Shelby County General Sessions and Criminal Court at 201 Poplar, in the Germantown, Bartlett, Collierville, and Millington municipal courts, and in the General Sessions courts of Fayette, Tipton, Lauderdale, and Haywood counties, and in the U.S. District Court for the Western District of Tennessee. Because the firm also handles divorce and family law, it can manage a domestic assault charge and the resulting divorce, custody, or order-of-protection proceedings together. Se habla Español.

## Start here

- [Memphis Criminal Defense Attorney](https://patrickbrookslaw.com/criminal-defense-2/): Criminal defense across Memphis, Shelby County, and the West Tennessee counties — misdemeanors through felonies, first appearance through trial. Flat fees quoted before you hire. Call (901) 324-5000.
- [What Happens After an Arrest in Memphis? The Shelby County Criminal Process](https://patrickbrookslaw.com/what-happens-after-arrest-memphis/): What happens after an arrest in Memphis: booking at 201 Poplar, bond, arraignment, preliminary hearing, grand jury, and what to do if you miss a court date.
- [How Does Bond Work in Memphis and Shelby County?](https://patrickbrookslaw.com/how-does-bond-work-memphis/): How bond gets set at 201 Poplar, the four ways to post it, what bonding companies don't tell you, and how a bond can be lowered. Free consultation: (901) 324-5000.
- [Capias and Bench Warrants in Shelby County: What They Mean and What to Do](https://patrickbrookslaw.com/capias-bench-warrant-shelby-county/): Missed a court date in Memphis? What a capias or bench warrant means, how it can be recalled — sometimes without an arrest — and what not to do. Call (901) 324-5000.
- [Warrants](https://patrickbrookslaw.com/warrant/): An outstanding warrant in Shelby County does not go away on its own. Brooks Law Firm helps clients address warrants and arrange a court date.
- [Courts We Serve](https://patrickbrookslaw.com/courts-we-serve/): Which court will hear your case? Shelby County 201 Poplar, Germantown, Bartlett, Collierville, and the West Tennessee county courts — schedules, differences, and what each docket means for your charge.
- [First-Time Offender in Memphis](https://patrickbrookslaw.com/first-time-offender-memphis/): A first charge in Memphis does not have to become a conviction. Dismissal, retirement, judicial and pretrial diversion, and expungement — which outcome to aim for and why it is not automatic.
- [Criminal Defense for Military Veterans in Tennessee](https://patrickbrookslaw.com/veterans-criminal-defense/): Charged with a crime and you served? Patrick Brooks was the public defender for the entire Shelby County Veterans Treatment Court docket. Treatment instead of conviction may be available. Call (901) 324-5000.

## Court process

- [What Happens at a Preliminary Hearing in Shelby County](https://patrickbrookslaw.com/preliminary-hearing-shelby-county/): What a preliminary hearing is, what happens in the courtroom at 201 Poplar, the possible outcomes, and why waiving one is rarely wise. Free consultation: (901) 324-5000.
- [What Happens at Arraignment in Shelby County General Sessions Court](https://patrickbrookslaw.com/arraignment-shelby-county/): Your first court date at 201 Poplar: what happens at a Shelby County General Sessions arraignment, how bond and conditions work, and what not to do. (901) 324-5000.
- [How Long Does a Criminal Case Take in Shelby County?](https://patrickbrookslaw.com/how-long-does-a-criminal-case-take-memphis/): Realistic Shelby County timelines — misdemeanors in General Sessions, felonies through the grand jury and Criminal Court — and what actually drives the pace. (901) 324-5000.
- [Memphis Misdemeanor Lawyer | Class A, B & C Charges in Shelby County](https://patrickbrookslaw.com/misdemeanor-defense/): Charged with a misdemeanor in Memphis or Shelby County? Class A, B & C penalties, diversion, expungement, and how cases resolve at 201 Poplar. Call (901) 324-5000.
- [Misdemeanor Citation vs. Arrest in Memphis: What That Ticket Really Means](https://patrickbrookslaw.com/misdemeanor-citation-memphis/): Got a misdemeanor citation instead of being arrested in Memphis? What T.C.A. § 40-7-118 citations mean, why the booking date matters, and what to do this week.
- [Misdemeanor Charges, Sentencing & Consequences in Tennessee](https://patrickbrookslaw.com/misdemeanors/): Tennessee Class A, B, and C misdemeanor sentencing ranges, probation length, the percentage-to-serve rule, diversion, expungement, and consequences, explained by Brooks Law Firm in Memphis. Call (901) 324-5000.
- [Memphis Felony Lawyer | When a Charge Becomes a Felony in Tennessee](https://patrickbrookslaw.com/felony-defense/): Felony charge in Memphis? How DUI, theft, drug, and domestic assault cases become felonies, the preliminary hearing at 201 Poplar, and paths to reduction. (901) 324-5000.
- [Can a Felony Be Reduced to a Misdemeanor in Tennessee?](https://patrickbrookslaw.com/felony-reduced-to-misdemeanor-tennessee/): How Tennessee felonies get reduced to misdemeanors: attacking the elevating element, the preliminary hearing window, negotiated amendments under § 40-35-113, and diversion.
- [Probation Violation](https://patrickbrookslaw.com/probation-violation/): Memphis probation violation defense from Brooks Law Firm. Hearing procedure under T.C.A. § 40-35-311, technical violation caps, Dagnan two-step inquiry, and key Tennessee case law. Call (901) 324-5000.
- [Memphis Expungement Lawyer | Clearing a Shelby County Record](https://patrickbrookslaw.com/expungement/): Clear a Memphis or Shelby County criminal record. Dismissals, diversions, and eligible convictions under T.C.A. 40-32-101. Call (901) 324-5000.
- [Can a Misdemeanor Be Expunged in Tennessee? The 2026 Rules](https://patrickbrookslaw.com/misdemeanor-expungement-tennessee/): Tennessee misdemeanor expungement explained: dismissed charges, completed diversions, and the § 40-32-101(g) five-year rule for convictions. Memphis-based help.
- [First-Time Offender in Memphis](https://patrickbrookslaw.com/first-time-offender-memphis/): A first charge in Memphis does not have to become a conviction. Dismissal, retirement, judicial and pretrial diversion, and expungement — which outcome to aim for and why it is not automatic.
- [Criminal Charges We Defend in Memphis and West Tennessee: DUI, Drugs, Domestic Assault &amp; More](https://patrickbrookslaw.com/criminal-charges-we-defend/): Overview of DUI, drug, domestic assault, theft, traffic & felony defense in Memphis, Germantown, Bartlett, Collierville, Fayette & Tipton County courts. Call (901) 324-5000.

## DUI

- [Memphis DUI Lawyer — DUI Defense in Shelby County, Tennessee](https://patrickbrookslaw.com/dui/): Arrested for DUI in Memphis? Patrick Brooks, member of the National College for DUI Defense, handles every DUI case personally — first offense through felony DUI, breath & blood test challenges, license reinstatement. Free consultation: (901) 324-5000.
- [First DUI in Memphis: What Happens Next and What to Do](https://patrickbrookslaw.com/first-dui-memphis/): Arrested for your first DUI in Memphis? Step-by-step: booking at 201 Poplar, your arraignment, penalties, and the three ways a first DUI can resolve. Free consultation: (901) 324-5000.
- [Refused the Breath Test in Memphis? What Happens to Your License](https://patrickbrookslaw.com/refused-breath-test-memphis-implied-consent/): A Tennessee breath test refusal creates a second case with its own deadline and its own license revocation. Winning the DUI does not fix it. (901) 412-2973.
- [Felony & Repeat DUI Defense in Memphis | 2nd, 3rd, 4th Offense | Brooks Law Firm](https://patrickbrookslaw.com/felony-dui/): Facing a 2nd, 3rd, or felony DUI in Memphis? Mandatory jail minimums, the 10-year lookback, and challenging prior convictions — Brooks Law Firm explains your options. Call (901) 324-5000.
- [Underage DUI Defense in Memphis | Drivers Under 21 | Brooks Law Firm](https://patrickbrookslaw.com/underage-dui/): Under 21 and charged with DUI or DWI in Memphis? Tennessee's limit is .02% for young drivers. Brooks Law Firm protects students' records and futures. Call (901) 324-5000.
- [Drug DUI Defense in Memphis | Driving Under the Influence of Drugs | Brooks Law Firm](https://patrickbrookslaw.com/drug-dui/): Charged with DUI for marijuana or prescription medication in Memphis? A positive blood test doesn't prove impairment. Brooks Law Firm defends drug DUI cases. Call (901) 324-5000.
- [DUI Breath & Blood Test Defense in Memphis | Challenging the Evidence | Brooks Law Firm](https://patrickbrookslaw.com/dui-breath-blood-tests/): Breathalyzer or blood test in a Memphis DUI? These results can be challenged — calibration, the 20-minute observation rule, chain of custody, and more. NCDD & TACDL member. Call (901) 324-5000.
- [Habitual Motor Vehicle Offender (HMVO) Removal in Tennessee](https://patrickbrookslaw.com/habitual-motor-vehicle-offender/): Still carrying a Habitual Motor Vehicle Offender designation? The HMVO law was repealed — Brooks Law Firm helps Memphis drivers remove the designation and reinstate their licenses. Call (901) 324-5000.
- [Leaving the Scene of an Accident in Tennessee &#8212; Memphis Defense Lawyer](https://patrickbrookslaw.com/leaving-scene-of-an-accident/): Charged with leaving the scene of an accident in Memphis? Brooks Law Firm explains Tennessee's duties after a crash, the penalties, and the defenses. Call (901) 324-5000.
- [Driving on a Suspended License in Memphis, Tennessee](https://patrickbrookslaw.com/suspended-license/): Charged with driving on a suspended license in Memphis? Brooks Law Firm defends the charge, pursues reinstatement, and handles restricted licenses. Call (901) 324-5000.
- [Waiving Old Court Costs &amp; Driver&#8217;s License Reinstatement in Tennessee](https://patrickbrookslaw.com/waiving-court-costs-license-reinstatement/): Lost your license over old court costs? Brooks Law Firm helps Memphis drivers waive old court debt and reinstate their Tennessee driver's licenses. Call (901) 324-5000.
- [First DUI in Germantown, TN: What Happens Next — and How to Protect Your Record](https://patrickbrookslaw.com/first-dui-germantown/): First DUI in Germantown, TN? Mandatory minimums, Germantown Municipal Court process, veterans treatment court dismissal, and the path to expungement. (901) 412-2973.
- [First DUI in Collierville, TN: Penalties, the Court Process, and Protecting Your Record](https://patrickbrookslaw.com/first-dui-collierville/): First DUI in Collierville, TN? Mandatory minimums, Collierville Municipal Court, the implied consent clock, and the veterans treatment court dismissal path.
- [First DUI in Bartlett, TN: What to Expect at the Justice Center and How to Keep Your Record Clean](https://patrickbrookslaw.com/first-dui-bartlett/): First DUI in Bartlett, TN? Why Bartlett's court is stricter, mandatory minimums, and the veterans treatment court path to dismissal and expungement. (901) 412-2973.
- [First DUI in Fayette County, TN: The Somerville Court Process and Protecting Your Record](https://patrickbrookslaw.com/first-dui-fayette-county/): First DUI in Fayette County, TN? The Somerville General Sessions process, I-40 stop defenses, mandatory minimums, and veteran options. (901) 412-2973.
- [First DUI in Tipton County, TN: The Covington Court Process and Protecting Your Record](https://patrickbrookslaw.com/first-dui-tipton-county/): First DUI in Tipton County, TN? The Covington Justice Center process, Hwy 51 commuter license stakes, mandatory minimums, and veteran options. (901) 412-2973.
- [Germantown DUI Attorney: What Happens After a DUI Arrest in Germantown, Tennessee](https://patrickbrookslaw.com/germantown-dui-attorney/): Germantown DUI attorney for the Wednesday 5 p.m. municipal court docket. Mandatory penalties, license & interlock rules, and real defenses. Call (901) 324-5000.
- [Collierville DUI Attorney: Defending Drunk Driving Charges in Collierville Municipal Court](https://patrickbrookslaw.com/collierville-dui-attorney/): Collierville DUI attorney for the 101 Walnut Street dockets. Hwy 385, Poplar & Hwy 72 stops, mandatory penalties, and why DUI can never be expunged. (901) 324-5000.
- [Bartlett DUI Attorney: Fighting DUI Charges in Bartlett City Court](https://patrickbrookslaw.com/bartlett-dui-attorney/): Bartlett DUI attorney for both City Court divisions at the Justice Center. Stage Rd & Hwy 64 stops, mandatory penalties, refusal & interlock rules. (901) 324-5000.

## Theft and property

- [Memphis Theft Attorneys](https://patrickbrookslaw.com/theft/): Charged with theft in Memphis or Shelby County? Flat fees from $750. Brooks Law Firm defends misdemeanor and felony theft and protects your record.
- [Memphis Shoplifting Lawyer](https://patrickbrookslaw.com/shoplifting/): Charged with shoplifting in Memphis? Theft under $1,000 is a misdemeanor at 201 Poplar. Keep it off your record. Flat fee from $750. Call (901) 324-5000.
- [Theft of Property Under $1,000 in Memphis](https://patrickbrookslaw.com/theft-of-property-under-1000-memphis/): Charged with theft of property under $1,000 in Memphis? It is a Class A misdemeanor in General Sessions. Penalties, diversion, and keeping it off your record.
- [Theft of Merchandise Under $1,000 in Memphis](https://patrickbrookslaw.com/theft-of-merchandise-under-1000-memphis/): Theft of merchandise under T.C.A. 39-14-146 in Memphis: what the statute covers, how value is graded, and which court hears the case. Call (901) 324-5000.
- [Felony Theft Over $1,000 in Memphis](https://patrickbrookslaw.com/felony-theft-over-1000-memphis/): Theft over $1,000 in Memphis is a Class E felony, 1 to 6 years. How value is proven, when it drops to a misdemeanor, and what it costs. Call (901) 324-5000.
- [Memphis Misdemeanor Theft Cases: What Actually Happens at 201 Poplar](https://patrickbrookslaw.com/memphis-misdemeanor-theft/): Theft under $1,000 in Memphis is a Class A misdemeanor in General Sessions. What the State must prove, the first setting, and how to avoid a conviction.
- [Will I Go to Jail for a Theft Charge in Memphis?](https://patrickbrookslaw.com/will-i-go-to-jail-for-a-theft-charge-memphis/): Jail is not the usual outcome on a first misdemeanor theft in Shelby County. What actually happens, what raises the risk, and what to do before your court date.
- [First Offense Theft in Tennessee: Diversion, Retirement, and Keeping It Off Your Record](https://patrickbrookslaw.com/first-offense-theft-tennessee-diversion/): A first theft charge in Tennessee can often end with no conviction. Judicial diversion, retirement, and expungement explained — and how each one can be lost.
- [The Civil Demand Letter After a Tennessee Shoplifting Charge](https://patrickbrookslaw.com/civil-demand-letter-shoplifting-tennessee/): Got a letter from a retailer's law firm demanding a few hundred dollars? What Tennessee's civil recovery statute allows, and why paying it does not end your criminal case.
- [Caught Shoplifting at Wolfchase: What Happens Next in Shelby County](https://patrickbrookslaw.com/caught-shoplifting-wolfchase-what-happens/): Stopped by loss prevention at a Wolfchase-area store? What happens from the back room to General Sessions, the civil demand letter, and how to protect your record.
- [Charged with Theft in Germantown: Which Court, and What Comes Next](https://patrickbrookslaw.com/theft-charge-germantown/): Germantown theft under $1,000 stays in municipal court; $1,000 and up goes to 201 Poplar. Why the valuation decides everything. Call (901) 324-5000.
- [Charged with Theft in Collierville: Which Court, and What Comes Next](https://patrickbrookslaw.com/theft-charge-collierville/): A Collierville theft citation stays in Collierville Municipal Court, not 201 Poplar. Carriage Crossing cases, the $1,000 line, and keeping it off your record.
- [Charged with Theft in Bartlett: Which Court, and What Comes Next](https://patrickbrookslaw.com/theft-charge-bartlett/): A Bartlett theft citation is heard in Bartlett City Court, not 201 Poplar. Why Bartlett runs strictly, the $1,000 line, and keeping it off your record.
- [Burglary](https://patrickbrookslaw.com/burglary/): Charged with burglary or aggravated burglary in Memphis? Brooks Law Firm explains Tennessee's burglary tiers, felony penalties, key terms, related trespass charges, and defenses. Call (901) 324-5000.
- [Robbery](https://patrickbrookslaw.com/robbery/): Charged with robbery or aggravated robbery in Memphis or Shelby County? Brooks Law Firm explains Tennessee's robbery laws, felony penalties, the 85% rule, enhancements, and defenses. Call (901) 324-5000.
- [White Collar Crime Defense](https://patrickbrookslaw.com/white-collar-crime-defense/): Facing fraud, embezzlement, or forgery charges in Memphis? Brooks Law Firm defends white collar cases in Shelby County and federal court.
- [Civil Asset Forfeiture Defense](https://patrickbrookslaw.com/civil-asset-forfeiture-defense/): Tennessee seized your car or cash? Brooks Law Firm contests civil asset forfeiture in Memphis — deadlines are short, so act quickly.

## Domestic assault and orders of protection

- [Memphis Domestic Assault Defense Lawyer](https://patrickbrookslaw.com/domestic-violence/): Charged with domestic assault in Memphis? Patrick Brooks has personally defended hundreds of domestic violence cases in the Shelby County DV courtroom — and handles the divorce & custody fallout under one roof. Free confidential consultation: (901) 324-5000.
- [Can Domestic Assault Charges Be Dropped in Memphis? What the Alleged Victim Can and Cannot Do](https://patrickbrookslaw.com/can-domestic-assault-charges-be-dropped-memphis/): The alleged victim cannot drop a domestic assault charge in Tennessee — but what they do still matters. Affidavits, subpoenas, and what ends these cases.
- [The First 72 Hours After a Domestic Assault Arrest in Memphis: The Hold, the Bond Conditions, and the Mistakes That Sink Cases](https://patrickbrookslaw.com/domestic-assault-arrest-memphis-first-72-hours/): The 12-hour hold, no-contact bond conditions, and the mistakes that sink Memphis domestic assault cases. What to do in the first 72 hours. Brooks Law Firm: (901) 324-5000.
- [Domestic Assault in Shelby County General Sessions Division 10: The Court, GPS Monitoring &amp; Penalties](https://patrickbrookslaw.com/shelby-county-division-10-domestic-assault/): Facing domestic assault in Shelby County's Division 10 at 201 Poplar? Bond conditions, GPS monitoring, penalties & how these cases get defended. (901) 324-5000.
- [Aggravated Domestic Assault in Memphis: When a Misdemeanor Argument Becomes a Felony Charge](https://patrickbrookslaw.com/aggravated-domestic-assault-memphis/): Strangulation, serious injury, or a weapon turns a Memphis domestic assault into a 3-15 year felony — often with no visible injury. How the charge works and how it's defended. (901) 324-5000.
- [Assault Charges in Memphis, Tennessee](https://patrickbrookslaw.com/assault/): Charged with assault in Memphis? Brooks Law Firm defends simple and aggravated assault charges in Shelby County and explains when a case becomes domestic assault. Call (901) 324-5000.
- [Orders of Protection in Memphis, Tennessee](https://patrickbrookslaw.com/order-of-protection/): Seeking or facing an order of protection in Memphis? Brooks Law Firm explains the Tennessee process, the evidence the court requires, controlling case law, and how to appeal under T.C.A. 36-3-601. Call (901) 324-5000.
- [Filing an Order of Protection in Memphis | Protect Yourself & Your Family | Brooks Law Firm](https://patrickbrookslaw.com/file-order-of-protection/): Need an order of protection in Memphis or Shelby County? Brooks Law Firm helps victims of domestic abuse, stalking, and harassment file and win protection hearings. No filing fee for victims. Call (901) 324-5000.
- [Domestic Assault Charges in Germantown: What Changes When the Arrest Happens in the Suburbs](https://patrickbrookslaw.com/germantown-domestic-assault-lawyer/): Domestic assault charge in Germantown TN? The 12-hour hold, no-contact conditions, custody fallout, and the dismissal-then-expunge strategy. Brooks Law Firm: (901) 324-5000.
- [Domestic Assault Charges in Bartlett: The 12-Hour Hold, the One-Building Court, and the Strategy That Protects Your Record](https://patrickbrookslaw.com/bartlett-domestic-assault-lawyer/): Domestic assault charge in Bartlett TN? The 12-hour hold, no-contact conditions, and why dismissal-then-expungement is the only goal worth having. Brooks Law Firm: (901) 324-5000.
- [Domestic Assault Charges in Collierville: What a Quiet Town's Loudest Charge Really Costs — and How the Defense Works](https://patrickbrookslaw.com/collierville-domestic-assault-lawyer/): Domestic assault charge in Collierville TN? The 12-hour hold, no-contact conditions, custody fallout, and the dismissal-then-expunge strategy. Brooks Law Firm: (901) 324-5000.
- [Electronic Monitoring](https://patrickbrookslaw.com/electronic-monitoring/): Tennessee GPS and electronic monitoring: which offenses require it, the 2024 mandatory domestic-violence GPS bond law, victim notification, costs, indigency, sex-offender and DUI monitoring, and violation defense. Memphis. Call (901) 324-5000.

## Drug charges

- [Memphis Drug Charge Lawyer — Drug Crime Defense in Shelby County](https://patrickbrookslaw.com/drug-offense/): Facing drug charges in Memphis? Suppression-first defense — unlawful stops and searches, drug-free zone enhancements, fentanyl weight, forfeiture, Drug Court. Free consultation: (901) 324-5000.
- [Arrested on Drug Charges in Memphis? What Happens Next — and the Three Decisions That Shape Your Case](https://patrickbrookslaw.com/drug-arrest-memphis-what-happens/): Arrested on drug charges in Memphis? What happens at 201 Poplar, the three decisions that shape your case, and when Drug Court beats a plea. Brooks Law Firm: (901) 324-5000.
- [Memphis Marijuana Charge Lawyer | Possession Defense | Brooks Law Firm](https://patrickbrookslaw.com/marijuana/): Cited or arrested on a marijuana charge in Memphis? Brooks Law Firm defends possession and sale cases and pursues diversion and expungement where eligible. Call (901) 324-5000.
- [Memphis Cocaine Charge Lawyer | Possession & Sale Defense | Brooks Law Firm](https://patrickbrookslaw.com/cocaine/): Charged with cocaine possession or sale in Memphis? Brooks Law Firm explains Tennessee's 0.5-gram felony threshold and defends Schedule II charges in Shelby County. Call (901) 324-5000.
- [Memphis Meth Charge Lawyer | Methamphetamine Defense | Brooks Law Firm](https://patrickbrookslaw.com/methamphetamine/): Arrested on a methamphetamine charge in Memphis? Brooks Law Firm defends possession, sale, and manufacturing cases under Tennessee's meth statutes. Call (901) 324-5000.
- [Memphis Heroin Charge Lawyer | Tennessee Heroin Defense | Brooks Law Firm](https://patrickbrookslaw.com/heroin/): Charged with heroin possession or delivery in Memphis? Brooks Law Firm defends Schedule I charges and pursues treatment-based resolutions where the evidence supports them. Call (901) 324-5000.
- [Fentanyl Charge Lawyer Memphis | Tennessee Fentanyl Defense | Brooks Law Firm](https://patrickbrookslaw.com/fentanyl/): Facing a fentanyl charge in Memphis? Brooks Law Firm defends fentanyl possession, sale, and death-resulting cases under Tennessee's toughened statutes. Call (901) 324-5000.
- [MDMA / Ecstasy Charge Lawyer Memphis | Brooks Law Firm](https://patrickbrookslaw.com/ecstasy/): Arrested on an ecstasy or MDMA charge in Memphis? Brooks Law Firm defends Schedule I charges, constructive-possession cases, and first-time defendants. Call (901) 324-5000.
- [Prescription Drug Charge Lawyer Memphis | Brooks Law Firm](https://patrickbrookslaw.com/prescription-pills/): Charged with prescription drug possession or fraud in Memphis? Brooks Law Firm defends pill cases, doctor-shopping allegations, and licensed professionals. Call (901) 324-5000.
- [Drug Conspiracy Defense in Memphis, Tennessee](https://patrickbrookslaw.com/drug-conspiracy/): Charged with drug conspiracy in Memphis or the Western District of Tennessee? Brooks Law Firm explains 21 U.S.C. 846 mandatory minimums, Tennessee conspiracy law, the evidence, case law, and defenses. Call (901) 324-5000.
- [Drug Testing Methodology and Challenges in Tennessee](https://patrickbrookslaw.com/drug-testing-methodology-defense/): Drug test results are not infallible. Brooks Law Firm challenges testing methodology, chain of custody, and lab error in Tennessee cases.
- [Shelby County Drug Court (General Sessions Division 8)](https://patrickbrookslaw.com/shelby-county-drug-court/): Shelby County Drug Court in General Sessions Division 8 can end an eligible drug case in dismissal and expungement. Brooks Law Firm evaluates every Memphis drug charge for Drug Court. (901) 324-5000.
- [Drug Charges in Germantown: Why Where Your Case Is Heard May Matter as Much as What You're Charged With](https://patrickbrookslaw.com/germantown-drug-charge-lawyer/): Drug charge in Germantown TN? Your case can resolve locally, through diversion, or via Shelby County Drug Court transfer — the choice shapes your record. Brooks Law Firm: (901) 324-5000.
- [Drug Charges in Bartlett: The One-Building Court, the Transfer Option Nobody Mentions, and How to Keep Your Record Clean](https://patrickbrookslaw.com/bartlett-drug-charge-lawyer/): Drug charge in Bartlett TN? The Shelby County Drug Court accepts Bartlett transfers, and diversion and suppression come first. Every option explained. Brooks Law Firm: (901) 324-5000.
- [Drug Charges in Collierville: The State-Line Problem, the Drug Court Door, and Protecting a Record Worth Protecting](https://patrickbrookslaw.com/collierville-drug-charge-lawyer/): Drug charge in Collierville TN? Suppression, diversion, and the Drug Court transfer Collierville cases qualify for — plus the Hwy 72 state-line wrinkle. Brooks Law Firm: (901) 324-5000.

## Traffic and CDL

- [Memphis Traffic Ticket Lawyer | Traffic Matters](https://patrickbrookslaw.com/traffic/): Traffic ticket in Memphis? Flat $200 per ticket at 201 Poplar, we go to court for you. Which court you're in, how Tennessee points work, and when you don't need a lawyer.
- [Memphis CDL Ticket Defense | Protect Your Commercial License](https://patrickbrookslaw.com/cdl-defense/): A CDL ticket in Memphis or Shelby County can cost your job. No traffic school, no diversion — federal law requires a real defense. Free consult: (901) 324-5000.
- [Memphis CDL &amp; Truck Driver Ticket Defense](https://patrickbrookslaw.com/cdl-defense-2/): CDL ticket in Memphis or Shelby County? We've handled thousands of tickets and defend hundreds every year. We appear in court for you — no time off the road. Free consultation: (901) 324-5000.
- [Germantown CDL Ticket Attorney](https://patrickbrookslaw.com/germantown-cdl-ticket/): CDL ticket in Germantown? We appear at Wednesday court for you — no time off the road. Thousands of tickets handled across Shelby County. Free consultation: (901) 324-5000.
- [CDL Ticket in Memphis? Why You Can't Take Driving School — and What Actually Works](https://patrickbrookslaw.com/cdl-ticket-memphis-what-to-do/): Federal law bars driving school and diversion for CDL tickets in Memphis. What works instead: dismissal, a real reduction below 15-over before judgment, or trial. (901) 324-5000.
- [CDL Ticket in Memphis: What Happens If You Just Pay It](https://patrickbrookslaw.com/cdl-ticket-memphis-what-happens-if-you-pay/): Paying a CDL ticket in Memphis is a guilty plea — reported to CDLIS within 10 days, visible to every carrier, and permanent. Read this before you pay. (901) 324-5000.
- [Speeding Tickets](https://patrickbrookslaw.com/speeding-tickets/): Memphis speeding ticket defense attorney. Brooks Law Firm contests speeding citations under T.C.A. 55-8-152, challenges radar and lidar evidence, and protects your license and insurance in Shelby County. Call (901) 412-2973.
- [Got a Speeding Ticket in Bartlett? Do You Have to Go to Court?](https://patrickbrookslaw.com/speeding-ticket-bartlett/): Bartlett City Court sets a date on every ticket, but we can appear for you. Why paying online costs more, the 12-point rule, and CDL warnings.
- [Reckless Driving Charges in Memphis, Tennessee](https://patrickbrookslaw.com/reckless-driving/): Charged with reckless driving in Memphis? Brooks Law Firm explains Tennessee's reckless driving law, penalties, and defenses — including DUI reductions. Call (901) 324-5000.
- [Failure to Maintain a Proper Lookout](https://patrickbrookslaw.com/failure-to-maintain-proper-lookout/): Memphis traffic ticket defense for failure to maintain a proper lookout (failure to exercise due care, T.C.A. 55-8-136). Brooks Law Firm contests citations and tries these cases in Shelby County. Call (901) 412-2973.
- [Failure to Move Over in Tennessee](https://patrickbrookslaw.com/failure-to-move-over-tennessee/): Tennessee's Move Over law is a Class B misdemeanor — more serious than most traffic tickets. What it requires, why these cases are defensible, and CDL risk.
- [Window Tint Ticket in Memphis](https://patrickbrookslaw.com/window-tint-ticket-memphis/): A Tennessee window tint citation is a Class C misdemeanor, not a fine. What the law requires, why the meter reading can be challenged, and what to do.
- [Expired or Improper Tags in Memphis](https://patrickbrookslaw.com/expired-tags-memphis/): Expired tags in Memphis are a Class C misdemeanor with a court date. Renewing before court often ends it — missing court turns it into a warrant.
- [Driving Without Insurance in Memphis](https://patrickbrookslaw.com/no-insurance-ticket-memphis/): A no-insurance citation in Memphis is a Class C misdemeanor with a separate Department of Safety track. If you were covered, proof usually ends the case.
- [Seat Belt Ticket in Memphis](https://patrickbrookslaw.com/seat-belt-ticket-memphis/): A Tennessee seat belt ticket carries no points for adult drivers. When it matters: if it came with a bigger charge from the same stop, or if you hold a CDL.

## Other charges

- [Unlawful Weapon Charges](https://patrickbrookslaw.com/unlawful-weapon/): Charged with unlawful possession or carrying of a weapon in Memphis? Brooks Law Firm defends Tennessee gun charges in Shelby County courts.
- [Sexual Offenses: Sentencing, Registry & Defense in Tennessee](https://patrickbrookslaw.com/sexual-offenses/): Tennessee sexual offense classes and sentence ranges, mandatory service percentages, case law, the sex offender registry, diversion-eligible offenses, and registry removal, explained by Brooks Law Firm in Memphis. Call (901) 324-5000.
- [Juvenile Defense](https://patrickbrookslaw.com/juvenile-defense/): Is your child charged in Shelby County Juvenile Court? Brooks Law Firm defends juvenile matters, transfer hearings, and record protection.
- [Disorderly Conduct Defense in Memphis, Tennessee](https://patrickbrookslaw.com/disorderly-conduct/): Charged with disorderly conduct in Memphis or Shelby County? Brooks Law Firm explains Tennessee's disorderly conduct law, penalties, related charges, defenses, and how to keep it off your record. Call (901) 324-5000.
- [Public Intoxication Defense in Memphis, Tennessee](https://patrickbrookslaw.com/public-intoxication/): Charged with public intoxication in Memphis? Brooks Law Firm explains why intoxication alone isn't a crime under T.C.A. 39-17-310, the elements, penalties, defenses, and how to keep it off your record. Call (901) 324-5000.
- [Selling or Furnishing Alcohol to a Minor in Tennessee](https://patrickbrookslaw.com/selling-alcohol-to-a-minor/): Charged with selling or furnishing alcohol to a minor in Memphis? Brooks Law Firm explains Tennessee penalties, the statutory ID defenses, the Responsible Vendor Program, and the beer-board and ABC licensing process. Call (901) 324-5000.
- [Selling or Furnishing Alcohol to a Minor | Germantown, Bartlett & Collierville, TN](https://patrickbrookslaw.com/selling-alcohol-to-minor/): Cited for selling or furnishing alcohol to a minor in Germantown, Bartlett, or Collierville? Sting defense, beer board issues, expungement-focused outcomes. (901) 412-2973.
- [Minor in Possession of Alcohol in Germantown, Bartlett & Collierville: What Parents Need to Know](https://patrickbrookslaw.com/minor-in-possession-suburbs/): Your teen or college student cited for minor in possession in Germantown, Bartlett, or Collierville? Dismissal-then-expungement is the goal. What parents should do. (901) 412-2973.
- [Beer Board & Liquor License Civil Penalties: Outcomes, Defenses, and Appeals](https://patrickbrookslaw.com/beer-and-liquor-board-penalties/): Tennessee beer board and ABC civil penalties, suspension and revocation outcomes, responsible-vendor protections, defenses, and appeal rights by certiorari and trial de novo. Memphis. Call (901) 324-5000.
- [Patronizing Prostitution Defense](https://patrickbrookslaw.com/patronizing-prostitution-defense/): Charged with patronizing prostitution in Memphis? Brooks Law Firm defends solicitation and sting cases in Shelby County — discreet representation.
- [False Offense Report](https://patrickbrookslaw.com/false-offense-report/): Charged with filing a false report in Tennessee? Brooks Law Firm defends false offense report cases in Memphis and Shelby County courts.
- [Accessory After the Fact](https://patrickbrookslaw.com/accessory-after-the-fact/): Charged as an accessory after the fact in Tennessee? Brooks Law Firm explains what the State must prove and defends the charge in Shelby County.
- [Contributing to the Delinquency of a Minor](https://patrickbrookslaw.com/contributing-to-delinquency-of-a-minor/): Charged with contributing to the delinquency of a minor in Tennessee? Brooks Law Firm defends the charge in Memphis and Shelby County.
- [Taking Contraband Into a Penal Facility](https://patrickbrookslaw.com/taking-contraband-into-a-penal-facility/): Charged with taking contraband into a jail or prison in Tennessee? Brooks Law Firm defends this felony charge in Shelby County courts.
- [Defending Protesters & the Right to Dissent in Tennessee](https://patrickbrookslaw.com/protest-civil-disobedience-defense/): Arrested at a protest in Memphis? Brooks Law Firm defends demonstrators facing disorderly conduct, obstruction, and trespass charges.
- [Employment Criminal Defense](https://patrickbrookslaw.com/employment-criminal-defense/): A criminal charge can cost you your job or license. Brooks Law Firm defends Memphis clients with careers and clearances on the line.
- [Law Enforcement Electronic Surveillance Defense](https://patrickbrookslaw.com/electronic-surveillance-defense/): Phone data, GPS, and digital surveillance evidence in Tennessee cases. Brooks Law Firm challenges how it was obtained and whether it holds up.
- [Criminal Appeals](https://patrickbrookslaw.com/criminal-appeal/): Appealing a Tennessee conviction? Brooks Law Firm handles criminal appeals and post-conviction matters from Shelby County in select cases.
- [Federal Criminal Defense](https://patrickbrookslaw.com/federal-criminal-defense/): Charged in the Western District of Tennessee? Brooks Law Firm defends federal criminal cases in Memphis — indictments, guidelines, and sentencing.
- [Criminal Defense for Military Veterans in Tennessee](https://patrickbrookslaw.com/veterans-criminal-defense/): Charged with a crime and you served? Patrick Brooks was the public defender for the entire Shelby County Veterans Treatment Court docket. Treatment instead of conviction may be available. Call (901) 324-5000.
- [Immigration Defense](https://patrickbrookslaw.com/immigration-defense/): Tennessee criminal defense for non-citizens. We defend immigrants facing criminal charges and protect immigration status by structuring pleas, sentences, and diversion with attention to removal and inadmissibility consequences.
- [Legislative Updates in Tennessee Criminal Law](https://patrickbrookslaw.com/legislative-updates-tennessee-criminal-law/): Recent changes to Tennessee criminal law and what they mean for defendants in Memphis and Shelby County, explained by Brooks Law Firm.

## Communities served

- [Germantown Municipal Court Criminal Defense](https://patrickbrookslaw.com/germantown-criminal-defense/): Criminal defense in Germantown Municipal Court — DUI, domestic assault, drug & theft charges. Court location, Wednesday 5 p.m. sessions & contact info. Call (901) 324-5000.
- [Bartlett City Court Criminal Defense](https://patrickbrookslaw.com/bartlett-criminal-defense/): Charged in Bartlett, TN? Defense for DUI, domestic assault, drug & theft cases in Bartlett City Court — Division I & II schedules, location & what to expect. (901) 324-5000.
- [Collierville Municipal Court Criminal Defense](https://patrickbrookslaw.com/collierville-criminal-defense/): Defense for DUI, domestic assault, drug & theft charges in Collierville Municipal Court, 101 Walnut Street. Court schedule, contact info & what to expect. (901) 324-5000.
- [Cordova Criminal Defense Lawyer | DUI, Drug &amp; Domestic Assault Charges](https://patrickbrookslaw.com/cordova-criminal-defense/): Arrested in Cordova, TN? DUI, drug, domestic assault & theft defense at 201 Poplar, Memphis City Court & Bartlett Municipal Court. Call (901) 324-5000.
- [Millington Criminal Defense Lawyer | DUI, Drug &amp; Domestic Assault Charges](https://patrickbrookslaw.com/millington-criminal-defense/): Arrested in Millington, TN? DUI, drug, domestic assault & theft defense in Millington Municipal Court & Shelby County — incl. NSA Mid-South personnel. (901) 324-5000.
- [Arlington Criminal Defense Lawyer | DUI, Drug & Domestic Charges](https://patrickbrookslaw.com/arlington-criminal-defense/): Arrested in Arlington, TN? DUI, drug, domestic assault & theft defense for Arlington cases in the Shelby County courts. Call (901) 324-5000.
- [Lakeland Criminal Defense Lawyer | DUI, Drug & Domestic Charges](https://patrickbrookslaw.com/lakeland-criminal-defense/): Arrested in Lakeland, TN? The Sheriff polices Lakeland and cases go to 201 Poplar. DUI, drug, domestic assault & theft defense. Call (901) 324-5000.
- [Arlington & Lakeland Criminal Defense Lawyer — Arrested in Northeast Shelby County? Your Case Goes to 201 Poplar](https://patrickbrookslaw.com/arlington-lakeland-criminal-defense/): Arrested in Arlington or Lakeland, TN? SCSO arrests from both towns are prosecuted at 201 Poplar in Memphis. DUI, drug, theft & domestic assault defense. Call (901) 324-5000.
- [Arrested or Charged in Oakland, Rossville, or Piperton? Here's Where Your Case Actually Goes](https://patrickbrookslaw.com/oakland-rossville-piperton-criminal-defense/): Arrested in Oakland, Rossville, or Piperton? Criminal charges go to Fayette County General Sessions in Somerville. Brooks Law Firm defends DUI, drug, domestic assault & theft charges there. (901) 324-5000.
- [Fayette County Criminal Court Defense](https://patrickbrookslaw.com/fayette-county-criminal-defense/): Criminal defense in Fayette County, TN — DUI, drug, domestic assault & theft charges in General Sessions & Circuit Court in Somerville. I-40 & Hwy 64 stops. (901) 324-5000.
- [Tipton County Criminal Court Defense](https://patrickbrookslaw.com/tipton-county-criminal-defense/): Criminal defense in Tipton County, TN — DUI, domestic assault, drug & theft charges in General Sessions & Circuit Court in Covington. Atoka, Munford, Brighton. (901) 324-5000.
- [Lauderdale County Criminal Court Defense](https://patrickbrookslaw.com/lauderdale-county-criminal-defense/): Criminal defense in Lauderdale County, TN — drug, DUI, domestic assault, theft & prison contraband charges in General Sessions & Circuit Court in Ripley. (901) 324-5000.
- [Haywood County Criminal Court Defense](https://patrickbrookslaw.com/haywood-county-criminal-defense/): Criminal defense in Haywood County, TN — DUI, drug, domestic assault & theft charges in General Sessions & Circuit Court in Brownsville. I-40 stops & BlueOval City area. (901) 324-5000.
- [Probation Violation Lawyer | Germantown, Bartlett & Collierville, TN](https://patrickbrookslaw.com/probation-violation-shelby-suburbs/): Misdemeanor probation violation defense in Germantown, Bartlett & Collierville municipal courts. VOP warrants, revocation hearings, technical violations. (901) 412-2973.
- [Probation Violation Lawyer | Somerville, Covington & Brownsville, TN](https://patrickbrookslaw.com/probation-violation-tri-county/): Probation violation defense in Fayette, Tipton & Haywood County General Sessions — Somerville, Covington & Brownsville. VOP warrants & revocation hearings. (901) 412-2973.
- [Missed a Drug Screen or Court Date on Probation in Germantown, Bartlett, or Collierville? Do This First](https://patrickbrookslaw.com/probation-violation-what-to-do-shelby-suburbs/): Missed a drug screen or court date on misdemeanor probation in Germantown, Bartlett, or Collierville? What happens next and what to do in the first week. (901) 412-2973.
- [Probation Violation Warrants in Fayette, Tipton & Haywood County: What Happens After the Warrant Issues](https://patrickbrookslaw.com/probation-violation-warrant-tri-county/): What happens after a probation violation warrant issues in Fayette, Tipton, or Haywood County — controlled surrender, revocation hearings, and fixes. (901) 412-2973.

## What it costs

- [How Much Does a Criminal Defense Lawyer Cost in Memphis?](https://patrickbrookslaw.com/how-much-does-a-criminal-defense-lawyer-cost-memphis/): Memphis criminal defense fees: misdemeanors from $750 in General Sessions at 201 Poplar, felonies from $1,500 to $2,500. Flat fees. Call (901) 324-5000.
- [How Much Does a DUI Lawyer Cost in Memphis?](https://patrickbrookslaw.com/how-much-does-a-dui-lawyer-cost-memphis/): What a DUI lawyer costs in Memphis: flat fees from $1,500 in General Sessions, $2,500 in the suburbs, $7,500-$10,000 at trial. Free consult: (901) 324-5000.
- [How Much Does a Theft Lawyer Cost in Memphis?](https://patrickbrookslaw.com/how-much-does-a-theft-lawyer-cost-memphis/): Theft lawyer fees in Memphis: from $750 for theft of property or merchandise under $1,000, from $1,500 for felony theft. Burglary and robbery quoted after consultation.
- [How Much Does a Drug Charge Lawyer Cost in Memphis?](https://patrickbrookslaw.com/how-much-does-a-drug-charge-lawyer-cost-memphis/): What a drug charge lawyer costs in Memphis: flat fees from $750 for misdemeanor possession, $1,500 for a felony. Free consultation: (901) 324-5000.
- [How Much Does a Domestic Assault Lawyer Cost in Memphis?](https://patrickbrookslaw.com/how-much-does-a-domestic-assault-lawyer-cost-memphis/): What a domestic assault lawyer costs in Memphis: flat fees from $750 for a misdemeanor, $1,500 for a felony. Free consultation: (901) 324-5000.
- [How Much Does a CDL Ticket Lawyer Cost in Memphis?](https://patrickbrookslaw.com/how-much-does-a-cdl-ticket-lawyer-cost-memphis/): CDL ticket lawyer fees in Memphis, published by court: $750 in Memphis and Shelby County. What it costs and why a CDL ticket must be fought. (901) 324-5000.
- [How Much Does a Traffic Ticket Lawyer Cost in Memphis?](https://patrickbrookslaw.com/how-much-does-a-traffic-ticket-lawyer-cost-memphis/): What a traffic ticket lawyer costs in Memphis: a flat per-ticket fee, we appear in court for you, and honest advice on whether you need one. (901) 324-5000.
- [How Much Does a Criminal Lawyer Cost in Bartlett, Germantown & Collierville?](https://patrickbrookslaw.com/how-much-does-a-criminal-lawyer-cost-bartlett-germantown-collierville/): What a criminal lawyer costs in Bartlett, Germantown, and Collierville: flat fees by charge, and why the suburban courts cost more than 201 Poplar. (901) 324-5000.

## Civil, family, and other practice

- [Civil Case](https://patrickbrookslaw.com/civil-litigation/): We provide experts and trial attorneys that fight to deliver esults you and your family deserve in a wrongful death or personal injury suit
- [Business Litigation](https://patrickbrookslaw.com/business-litigation-2/): Contract, partnership, and commercial disputes in Memphis and Shelby County. Brooks Law Firm represents businesses in Circuit and Chancery Court.
- [Personal Injury — Motor Vehicle Accidents](https://patrickbrookslaw.com/personal-injury/): Injured in a Tennessee car, truck, or motorcycle accident? Brooks Law Firm handles motor vehicle personal injury cases in Memphis on contingency — no fee unless we recover. Call (901) 324-5000.
- [Wrongful Death](https://patrickbrookslaw.com/wrongful-death-2/): Tennessee wrongful death representation for Memphis and Shelby County families — one-year deadline under T.C.A. § 20-5-113. Brooks Law Firm. Call (901) 324-5000.
- [Divorce](https://patrickbrookslaw.com/divorce/): Tennessee divorce representation in Memphis and Shelby County — uncontested, contested, and high-asset cases. Brooks Law Firm. Call (901) 324-5000.
- [Uncontested Divorce in Tennessee](https://patrickbrookslaw.com/uncontested-divorce/): Agreed divorce in Tennessee — fast, flat-fee uncontested divorce for Memphis and Shelby County couples. Brooks Law Firm drafts the MDA and parenting plan. Call (901) 324-5000.
- [Contested Divorce in Tennessee](https://patrickbrookslaw.com/contested-divorce/): Contested divorce in Memphis and Shelby County — custody, alimony, property, and support disputes through discovery, mediation, and trial. Brooks Law Firm. Call (901) 324-5000.
- [High-Income & High-Asset Divorce in Tennessee](https://patrickbrookslaw.com/high-income-divorce/): High-income and high-asset divorce in Memphis and Shelby County — business valuation, RSUs, executive comp, hidden income, tax-aware property division. Brooks Law Firm. Call (901) 324-5000.
- [Intellectual Property Protection](https://patrickbrookslaw.com/intellectual-property/): Trademark, copyright, and licensing matters for Memphis businesses and creators. Brooks Law Firm handles registration and disputes.
- [&#x1f3b6; Music &amp; Artist Reps &#x1f3ad;](https://patrickbrookslaw.com/music-artist-representation/): Contracts, licensing, royalties, and rights for Memphis musicians and artists. Brooks Law Firm represents talent and independent labels.
- [Maritime and Admiralty Law](https://patrickbrookslaw.com/maritime-law/): Maritime and admiralty matters on the Mississippi River and inland waterways. Brooks Law Firm represents crew and operators from Memphis.

## En español

- [Defensa de Inmigración](https://patrickbrookslaw.com/defensa-de-inmigracion/): Defensa penal en Tennessee para no ciudadanos. Defendemos a inmigrantes acusados de delitos y protegemos su estatus migratorio estructurando declaraciones de culpabilidad, sentencias, y programas de desvío con atención a las consecuencias de expulsión e inadmisibilidad.
- [Recursos de Inmigración](https://patrickbrookslaw.com/recursos-legales-de-inmigracion/): Directorio enfocado en Memphis de ayuda legal de inmigración, reasentamiento de refugiados, alimentos y apoyo en crisis, salud, clases de inglés y servicios para víctimas, además de recursos estatales y nacionales sin fines de lucro.

## The firm

- [Firm Profile](https://patrickbrookslaw.com/firm-profile-3/): Brooks Law Firm is a Midtown Memphis practice handling criminal defense, civil litigation, appeals, family, disability, and estate matters in Shelby County. Call (901) 324-5000.
- [Patrick Brooks](https://patrickbrookslaw.com/patrick-brooks-profile/): Patrick Brooks is a Memphis criminal defense attorney handling DUI, drug, assault, theft, and weapons matters in Shelby County and West Tennessee. Call (901) 324-5000.
- [Beth Brooks](https://patrickbrookslaw.com/beth-brooks-profile/): Beth Brooks is a Memphis attorney handling family law, juvenile matters, estate planning, Social Security disability, civil rights, personal injury, and workers' compensation. Call (901) 324-5000.
- [Robert Brooks](https://patrickbrookslaw.com/robert-brooks/): Robert Brooks is a Memphis criminal appellate attorney with nearly 40 years of experience handling state and federal appeals, post-conviction, and habeas corpus. Call (901) 324-5000.
- [Contact](https://patrickbrookslaw.com/contact-updated/): Contact Brooks Law Firm at 2299 Union Avenue in Midtown Memphis. Office (901) 324-5000; criminal direct line (901) 412-2973; patrick@patrickbrookslaw.com. Se habla Español.
- [Legal Resources](https://patrickbrookslaw.com/resources-updated/): Directory of Tennessee and federal legal resources, courts, clerks, agencies, bar associations, and legal aid organizations. Maintained by Brooks Law Firm in Memphis.
- [Immigration Resources](https://patrickbrookslaw.com/immigration-resources/): Memphis-focused directory of immigration legal aid, refugee resettlement, food and crisis support, healthcare, ESL, and victim services for immigrants, plus statewide and national nonprofit resources.
- [Legal Insights from Brooks Law Firm | Memphis Criminal Defense Blog](https://patrickbrookslaw.com/blog/): Articles from Brooks Law Firm on Tennessee criminal law: DUI, drug charges, domestic assault, probation violations, and traffic matters in Memphis and West Tennessee.
BROOKS_LLMS_DEFAULT;
}

/**
 * Return the content to serve: saved option if present, else the default.
 */
function brooks_llms_get_content() {
	// Suite Pro 5: automatic mode builds the body from live content, so the
	// file can never drift out of date as the site grows. See llms-auto.php.
	if ( function_exists( 'brooks_llms_mode' ) && 'auto' === brooks_llms_mode() ) {
		return brooks_llms_generated_content();
	}

	$content = get_option( BROOKS_LLMS_OPTION );
	if ( ! is_string( $content ) || '' === trim( $content ) ) {
		return brooks_llms_default_content();
	}
	return $content;
}

/**
 * Serve /llms.txt early, before WordPress resolves the request to a 404.
 * Hooks 'init' at priority 0 so it runs ahead of the plugin's own
 * 404-gated redirect fallbacks.
 */
function brooks_llms_maybe_serve() {
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}

	// Compare only the path, ignoring any query string, with/without slash.
	$path = wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- unslashed and sanitized inline.
	if ( null === $path ) {
		return;
	}
	$path = strtolower( rtrim( $path, '/' ) );
	if ( '/llms.txt' !== $path ) {
		return;
	}

	/*
	 * A static file at the web root used to win here. That produced a silent
	 * failure mode: edits saved, nothing changed, and nothing said why. The
	 * plugin now always serves its own content; if a static file exists the
	 * settings screen reports it so the conflict is visible.
	 */

	header( 'Content-Type: text/plain; charset=utf-8' );
	/*
	 * Declared here rather than left to the theme's security-headers filter:
	 * that filter runs in WP::send_headers(), and this handler answers on
	 * init and exits long before it, so the response would otherwise ship
	 * without it. The body is stored unescaped on purpose (escaping would
	 * corrupt the Markdown), which is exactly when the browser must be told
	 * not to second-guess the declared type.
	 */
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Cache-Control: public, max-age=3600' );
	echo brooks_llms_get_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- text/plain body; HTML escaping would corrupt the Markdown.
	exit;
}
add_action( 'init', 'brooks_llms_maybe_serve', 0 );

/**
 * Settings page under Settings → LLMs.txt.
 */
function brooks_llms_admin_menu() {
	add_options_page(
		'LLMs.txt',
		'LLMs.txt',
		'manage_options',
		'brooks-llms-txt',
		'brooks_llms_render_settings'
	);
}
add_action( 'admin_menu', 'brooks_llms_admin_menu' );

function brooks_llms_register_setting() {
	register_setting(
		'brooks_llms_group',
		BROOKS_LLMS_OPTION,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'brooks_llms_sanitize',
			'default'           => '',
		)
	);
}
add_action( 'admin_init', 'brooks_llms_register_setting' );

/**
 * Suite Pro 5: register the automatic-mode options.
 */
function brooks_llms_register_auto_settings() {
	register_setting(
		'brooks_llms_group',
		BROOKS_LLMS_MODE,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'brooks_llms_sanitize_mode',
			'default'           => 'manual',
		)
	);
	register_setting(
		'brooks_llms_group',
		BROOKS_LLMS_HEADER,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'brooks_llms_sanitize',
			'default'           => '',
		)
	);
}
add_action( 'admin_init', 'brooks_llms_register_auto_settings' );

/**
 * Only two modes are valid.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function brooks_llms_sanitize_mode( $value ) {
	return ( 'manual' === $value ) ? 'manual' : 'auto';
}

/**
 * Store verbatim: normalize line endings, strip null bytes, keep everything
 * else. Safe because output is text/plain, never rendered as HTML.
 */
function brooks_llms_sanitize( $value ) {
	if ( ! is_string( $value ) ) {
		return '';
	}
	$value = str_replace( "\0", '', $value );
	$value = str_replace( array( "\r\n", "\r" ), "\n", $value );
	return $value;
}

function brooks_llms_render_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$saved   = get_option( BROOKS_LLMS_OPTION );
	$is_default = ( ! is_string( $saved ) || '' === trim( $saved ) );
	// Never pre-fill the editable box with the baked default: on this host a
	// large form field is silently truncated, so saving the pre-filled box
	// would store a clipped copy that then overrides the intact default.
	$current = $is_default ? '' : $saved;
	$url     = home_url( '/llms.txt' );
	$mode    = function_exists( 'brooks_llms_mode' ) ? brooks_llms_mode() : 'manual';
	$header  = function_exists( 'brooks_llms_header' ) ? brooks_llms_header() : '';
	$count   = 0;
	if ( 'auto' === $mode && function_exists( 'brooks_llms_generated_content' ) ) {
		$count = substr_count( brooks_llms_generated_content(), "\n- [" );
	}
	?>
	<?php
	$served     = brooks_llms_get_content();
	$static     = file_exists( ABSPATH . 'llms.txt' );
	$manual_len = is_string( $saved ) ? strlen( $saved ) : 0;
	$header_len = strlen( (string) $header );
	if ( 'auto' === $mode ) {
		$source = 'Automatic — header block above the generated page list. The Manual body box is IGNORED.';
	} else {
		$source = $is_default
			? 'Manual — but the Manual body box is empty, so the bundled default is being served. The Header block is IGNORED.'
			: 'Manual — the Manual body box below. The Header block is IGNORED.';
	}
	?>
	<div class="wrap">
		<h1>LLMs.txt</h1>

		<div class="notice notice-info" style="padding:12px 14px;">
			<p style="margin:0 0 6px;"><strong>Now serving:</strong> <?php echo esc_html( $source ); ?></p>
			<p style="margin:0 0 6px;">
				<strong>Live file:</strong>
				<a href="<?php echo esc_url( add_query_arg( 'cb', time(), $url ) ); ?>" target="_blank" rel="noopener">open /llms.txt (cache-busted)</a>
				— <?php echo esc_html( number_format_i18n( strlen( $served ) ) ); ?> bytes,
				<?php echo esc_html( number_format_i18n( substr_count( $served, "\n- " ) ) ); ?> links.
			</p>
			<p style="margin:0;">
				Stored: Header block <?php echo esc_html( number_format_i18n( $header_len ) ); ?> bytes ·
				Manual body <?php echo esc_html( number_format_i18n( $manual_len ) ); ?> bytes.
				If a box looks shorter than what you pasted, the save was truncated by the server — tell your host.
			</p>
		</div>

		<?php if ( $static ) : ?>
			<div class="notice notice-warning" style="padding:12px 14px;">
				<p style="margin:0;"><strong>A static file exists at <code><?php echo esc_html( ABSPATH . 'llms.txt' ); ?></code>.</strong>
				This plugin still serves its own content, but depending on your server configuration the static file may be
				returned first. Delete or rename it to remove any doubt.</p>
			</div>
		<?php endif; ?>

		<p><strong>After saving, purge Cloudflare</strong> so the edge serves the new version.</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'brooks_llms_group' ); ?>

			<h2>How the page list is built</h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">Mode</th>
					<td>
						<label>
							<input type="radio" name="<?php echo esc_attr( BROOKS_LLMS_MODE ); ?>" value="auto" <?php checked( $mode, 'auto' ); ?> />
							<strong>Automatic</strong> — build the page list from published pages
						</label>
						<p class="description">
							Every published, indexable page is listed and grouped automatically. Publish a page and it appears; unpublish it and it goes away. Nothing to maintain by hand.
							<?php if ( 'auto' === $mode && $count ) : ?>
								<br /><strong><?php echo esc_html( (string) $count ); ?></strong> pages currently listed.
							<?php endif; ?>
						</p>
						<label>
							<input type="radio" name="<?php echo esc_attr( BROOKS_LLMS_MODE ); ?>" value="manual" <?php checked( $mode, 'manual' ); ?> />
							<strong>Manual</strong> — serve exactly what is in the box below
						</label>
						<p class="description">Full control, but the list will not update itself as the site grows.</p>
					</td>
				</tr>
			</table>

			<h2>Header block <?php echo 'auto' === $mode ? '<span style="color:#0a0;">(IN USE)</span>' : '<span style="color:#a00;">(NOT IN USE — automatic mode is off)</span>'; ?></h2>
			<p class="description">
				<strong>Automatic mode only.</strong> The firm description, address and phones that appear above the
				generated page list. Do not paste a full llms.txt file here — in manual mode this box is ignored entirely.
			</p>
			<textarea
				name="<?php echo esc_attr( BROOKS_LLMS_HEADER ); ?>"
				rows="10"
				style="width:100%;font-family:Menlo,Consolas,monospace;font-size:13px;"
				spellcheck="false"><?php echo esc_textarea( $header ); ?></textarea>

			<h2>Manual body <?php echo 'manual' === $mode ? '<span style="color:#0a0;">(IN USE)</span>' : '<span style="color:#a00;">(NOT IN USE — automatic mode is on)</span>'; ?></h2>
			<p class="description">
				<strong>Manual mode only.</strong> Served verbatim, exactly as typed. <strong>Leave this box empty to serve the
				bundled default</strong> (the full curated site map baked into the plugin). Anything typed here replaces it.
				On this host a single form field is truncated at about 6.4 KB, so do not paste a large file here — use the
				upload or chunked save below instead.
			</p>
			<?php if ( $is_default ) : ?>
			<details style="margin:8px 0 12px;">
				<summary style="cursor:pointer;">Preview the bundled default now being served (read-only, <?php echo esc_html( number_format_i18n( strlen( brooks_llms_default_content() ) ) ); ?> bytes)</summary>
				<pre style="max-height:320px;overflow:auto;background:#f6f7f7;padding:10px;font-size:12px;white-space:pre-wrap;"><?php echo esc_html( brooks_llms_default_content() ); ?></pre>
			</details>
			<?php endif; ?>
			<textarea
				name="<?php echo esc_attr( BROOKS_LLMS_OPTION ); ?>"
				rows="20"
				style="width:100%;font-family:Menlo,Consolas,monospace;font-size:13px;"
				spellcheck="false"><?php echo esc_textarea( $current ); ?></textarea>
			<?php submit_button( 'Save llms.txt' ); ?>
		</form>

		<?php
		if ( function_exists( 'brooks_llms_render_bigsave' ) ) {
			settings_errors( 'brooks_llms' );
			brooks_llms_render_bigsave();
		}
		?>
	</div>
	<?php
}
