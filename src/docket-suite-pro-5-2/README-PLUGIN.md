# Docket Suite Pro 5 (5.0.0)

## 5.2.2 — 2026-09-02

**Fix (diagnostics false positive).** `url_to_postid()` returns 0 for the page assigned as the Posts page, so every rule pointing at `/blog/` was reported as a broken destination. Added `brooks_ess_path_is_live()`, which also checks `get_page_by_path()` and the `page_for_posts` permalink before calling a destination dead.

## 5.2.1 — 2026-09-02

**Redirect diagnostics.** The "N lines could not be read as rules" notice was computed as (rule lines − map entries), so any two lines that normalise to the same source — `/foo` and `/foo/`, or two spellings of a path — were counted as unreadable even though both parsed. Replaced with `brooks_ess_rule_diagnostics()`, which reports four separate, accurate groups on the settings screen: **malformed** lines (not active), **broken destinations** (active rules that land on a 404), **duplicate sources** (harmless), and **dead weight** (sources that are live pages, so a fallback rule can never fire). Each row shows the line number, the line, and why.

**Baked rules.** Three destinations pointed at `/patrick-brooks/` and `/beth-brooks/`, which do not exist; corrected to `/patrick-brooks-profile/` and `/beth-brooks-profile/`. Removed `/robert-brooks/ => /firm-profile-3/`, since `/robert-brooks/` is now a published page. (Baked rules are applied once on the 2.0 migration; these fixes affect fresh installs, not the live textarea.)

## 5.2.0 — 2026-09-02

**Fix (activation fatal).** The dormancy guard only detected a Brooks Law Essentials copy that had *already* loaded in the same request. When this plugin's folder sorted first, Essentials loaded after it and hit `Cannot redeclare brooks_ess_defaults()`, which WordPress reported against the plugin being activated. The activation takeover also matched Essentials by folder name only (`brooks-law-essentials`), so a copy under any other directory name was never stood down.

- `docket_suite_conflicting_plugins()` detects Essentials by symbol (`function brooks_ess_defaults` in the main file or `includes/core.php`), independent of folder name. Result is cached against a fingerprint of the active-plugin list: one option read per request in steady state, no filesystem access.
- Load-order guard: if a conflicting copy is still on the active list, this plugin stays dormant for that request instead of declaring the shared symbols. `admin_init` self-heal then deactivates the other copy; the next request runs alone.
- Dormancy check broadened to four signals.
- `DOCKET_SUITE_VERSION` constant was still `5.1.4`; now tracks the header.

**llms.txt.** Baked default regenerated from the live site: 167 entries (was 163). Adds `/first-time-offender-memphis/`, promotes the veterans hub into *Start here*, adds Robert Brooks; 67 existing entries had drifted from current Yoast titles/descriptions and were refreshed. Header credentials updated (thousands of criminal cases, Veterans Treatment Court assignment, NCDD/TACDL/MBA, licensed 2012, Western District).

**Hygiene.** `REQUEST_URI` reads now pass through `sanitize_text_field()` (redirects, early-redirects, seo); two Yoda conditions in spc-watchdog; explicit `phpcs:ignore` with rationale on the two intentional exceptions (text/plain llms body; display-only `$_GET` flag). `phpcs.xml.dist` added so future checks run against the same ruleset.


All-in-one site operations + SEO. Built on the **Brooks Law Essentials 3.0.2**
modules, verbatim, with the SEO half added beside them.

First build intended for a live site. It supersedes the Docket Suite 1.0.0
prototype, which was never installed anywhere.

## Why the 1.0.0 prototype was not usable

1.0.0 was assembled from Essentials **2.0.0**. Installing it on a 3.0.2 site
would have rolled the operational half backwards:

| Module | Essentials 3.0.2 | Prototype 1.0.0 | Pro 5 |
|---|---|---|---|
| core.php | 139 lines | missing | 139 lines |
| early-redirects.php | 149 lines | missing | 149 lines |
| crawlers.php (robots.txt manager) | 392 lines | 76 lines | 392 lines |
| baked-rules.php | 133 rules | 98 rules | 133 rules |
| settings.php | 421 lines | 369 lines | 421 lines |
| shortcodes.php | 245 lines | 202 lines | 245 lines |
| spc-watchdog.php | 122 lines | 71 lines | 122 lines |

Nothing operational in Pro 5 is older than what is already live.

## Collisions found and resolved

**robots.txt had two writers.** `crawlers.php` writes and heals a physical
robots.txt (filter priority 99) with its own `Sitemap:` line; `seo.php`
registered a second `robots_txt` filter at priority 10 adding another. The
seo.php hook is removed. `crawlers.php` is the single authority and now
recognises `DOCKET_SUITE_SEO_ACTIVE`, so the advertised sitemap follows
whichever engine is live:

* Yoast active → `/sitemap_index.xml`
* Suite SEO live → `/sitemap.xml`

**Schema — no collision, and none introduced.** `seo.php` contains zero
JSON-LD. The theme's `inc/schema-graph.php` owns the full @graph and
`inc/faq-schema.php` the FAQ blocks. Schema stays entirely theme-side.

**Sitemap ownership.** `seo.php` disables core `wp_sitemaps`, claims
`/sitemap.xml` only when no other SEO plugin is active, and 301s the legacy
Yoast sitemap URLs so nothing 404s after the cutover.

## Your Yoast metadata is not at risk

`seo.php` already reads Yoast's own keys at render time when its fields are
empty — `_yoast_wpseo_title`, `_yoast_wpseo_metadesc`,
`_yoast_wpseo_meta-robots-noindex` — skipping `%%template%%` values.
**Deactivating Yoast does not delete that post meta**, so every custom title
and description keeps working with or without a migration.

The new **Settings → Docket SEO → Import from Yoast** panel is optional. It
copies those values into Docket's own fields so they appear in the editor and
stop depending on Yoast-shaped data. It never overwrites an existing Docket
value, skips template values, records a per-post backup, and has an Undo
button. Preview first — it writes nothing.

Focus keywords and readability scores are deliberately not migrated: they are
Yoast-only scoring artifacts with no front-end output.

## Two gaps found in the crawler/AI review, and closed

**Category archives were missing from the sitemap.** `seo.php` listed pages
and posts only. Yoast lists category archives today, and this site's three
populated categories (Criminal Defense, DUI, Traffic) are indexable by
design — so the cutover would have silently dropped three live URLs from the
sitemap. They are now included, with `lastmod` taken from the most recently
modified post in each. Empty categories are always skipped. Toggle:
*Include category archives that have posts* (on by default).

**IndexNow had no replacement.** Yoast Premium pings IndexNow on every
publish and update — its `_yoast_indexnow_last_ping` meta is on pages all
over this site. Retiring Yoast without replacing it would remove the only
push-based discovery channel here. `includes/indexnow.php` restores it: one
submission notifies Bing, Yandex, Seznam and Naver, which matters because
Copilot and DuckDuckGo read Bing's index — the shortest path from "published"
to "an AI assistant can cite it".

Google does not participate in IndexNow and is unaffected either way; Google
discovery stays with the sitemap and Search Console.

Implementation notes: the verification key is generated once and served from
a rewrite at `/<key>.txt` (no file written to disk), submissions are
non-blocking so a slow endpoint can never delay a post save, each URL is
debounced for an hour, and drafts, private, password-protected, revision and
noindexed content are never submitted. **Off by default** — turn it on after
Yoast is deactivated. It is also inert automatically while any other SEO
plugin is active, so it cannot double-submit even if switched on early.

## /llms.txt now builds itself

The hand-maintained llms.txt body had drifted badly: it listed 50 URLs while
the site had grown to 157, so the file that tells AI assistants what is worth
reading was pointing at roughly a third of the site — missing the entire
theft, process, felony, misdemeanor and cost clusters. A hand-maintained list
of a growing site is a list that is always out of date.

`includes/llms-auto.php` generates the body from live content instead.
Settings → LLMs.txt now offers:

* **Manual** (default) — the editable box, unchanged, for full control.
* **Automatic** — every published, indexable page is listed and grouped into
  sections automatically. Publish a page and it appears; unpublish it and it
  goes away.

**Manual is the default on purpose.** llms.txt rewards curation, not
completeness: it is a guide for a reader with limited attention, so a short
hand-written list with real descriptions — published flat fees, court-by-court
pricing, the facts an assistant needs to cite the firm accurately — beats an
exhaustive generated dump of 154 pages. Automatic mode exists for sites with
no curated file, or when a list has outgrown hand maintenance. It should never
silently replace a good one.

The bundled default is now the full curated file (60 entries), so a fresh
install ships with the real thing rather than a stub.

The header block (firm description, address, phones, hours) stays
hand-written and editable in either mode. Only the page list is generated,
because no generator writes an introduction well.

Details:

* Sections are matched by URL pattern, so the output reads like the curated
  file rather than a flat dump. Anything matching no pattern lands in "More
  From the Firm" — nothing is silently dropped. The map is filterable via
  `brooks_llms_sections`.
* Each description falls back through Docket field → Yoast field → excerpt →
  trimmed opening, so no entry is ever bare.
* The static front page is skipped (the header already introduces the site),
  as are noindexed and password-protected pages — matching sitemap behaviour.
* Output is cached in a transient and flushed on save, delete, trash and
  untrash, so serving /llms.txt never runs the query.

Verified against a 157-page export: 154 pages grouped into 11 sections, 3
landing in the overflow section, none lost.

## What is intentionally NOT duplicated

* **Schema** — the theme owns it entirely. `seo.php` emits no JSON-LD.
* **Breadcrumbs** — `schema-graph.php` emits BreadcrumbList.
* **FAQ blocks** — `faq-schema.php`, with `schema-repair.php` fixing
  wpautop-broken JSON-LD at render time.
* **Redirects** — `redirects.php` + `early-redirects.php`.
* **robots.txt** — `crawlers.php`, single authority.
* **/llms.txt** — `llms-txt.php`, single owner.

## Verification after cutover

| Check | Where | Expect |
|---|---|---|
| Title + description | View source, 5 pages | One of each, your copy |
| Canonical | View source | Exactly one `rel=canonical` |
| Schema | Rich Results Test | One LegalService, no duplicates |
| Sitemap | `/sitemap.xml` | Pages, posts, 3 category archives, `lastmod` present |
| Legacy sitemap | `/sitemap_index.xml` | 301 → `/sitemap.xml`, not a 404 |
| robots.txt | `/robots.txt` | One `Sitemap:` line, pointing at `/sitemap.xml` |
| llms.txt | `/llms.txt` | Unchanged |
| AI crawlers | `curl -A "GPTBot" -I` on a page | 200 |
| IndexNow key | `/<key>.txt` | Plain-text key (only once enabled) |
| GSC | Sitemaps | Submit `/sitemap.xml`; old one may read "couldn't fetch" for a few days — normal |
| Bing Webmaster | Sitemaps | Submit `/sitemap.xml` there too |

## Installing

Docket Suite Pro 5 has not been installed on this site before, so there is no
earlier Suite copy to coordinate with — the only handoff is from Brooks Law
Essentials 3.0.2, which is live today.

The plugin deactivates any active Brooks Law Essentials copy on activation
(silently — shared options are untouched, so rules, 404 log, settings and
llms.txt all carry over). Leave the old plugin **deactivated but installed**
as a rollback. Do not use the WordPress *Delete* link on it: uninstall would
erase the shared options.

## Cutover order

1. Full backup.
2. Upload and activate Docket Suite. Confirm the handoff notice, and confirm
   the **"Docket SEO is standing down"** notice — that proves both halves are
   loaded and Yoast is still authoritative.
3. Spot-check the site logged out. Redirects, robots.txt, /llms.txt should be
   unchanged.
4. Optional: Settings → Docket SEO → Preview, then Import from Yoast.
5. Deactivate Yoast. The stand-down notice disappears and the SEO half takes
   over.
6. View source on five pages; confirm titles, descriptions and one canonical
   tag each — and exactly one LegalService block from the theme.
7. Purge Cloudflare. Submit `/sitemap.xml` in Search Console.
8. Keep Yoast installed-but-inactive for a week as rollback.

Rolling back is reactivating Yoast: the SEO half stands down again on the next
request.
