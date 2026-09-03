# Fixes applied — theme 5.3.0, plugin 5.2.3

Every item traces to a finding in `../reviews/`. Each is listed with how it was
verified; **executed** means a test was run against the shipped function, not
that the code was read.

## Theme — performance layer

| Was | Now | Verified |
|-----|-----|----------|
| `wp_cache_flush()` on every save discarded the whole object cache | Generational cache keys: a purge is one option write plus a bounded `delete_transient()` sweep. No flush. | review |
| HTML minifier's `<!--BLFPROTECT0-->` token was forgeable from page content | Per-request random marker; a forged token is stripped as an ordinary comment | executed |
| Blanket `\s{2,}` pass rewrote attribute values | Quote-aware tag split; whitespace collapses in text nodes only | executed — `alt`, `title`, `aria-label`, `data-` JSON and `>` inside an attribute all byte-identical |
| CSS minifier ate spaces inside quoted strings | Strings and `url()` lifted out before any whitespace pass; `/*!` licence comments kept | executed |
| *(introduced then caught during this work)* the string-protection regex exhausted the PCRE JIT stack on the two largest sheets, silently returning them unminified | Unrolled-loop form, linear | executed — style.css 45.5 KB → 12.1 KB |
| No `Vary`/`Cache-Control`, `DONOTCACHEPAGE` ignored | All three added, plus a `brooks_law_cacheable` filter | review |
| `/uploads/brooks-law-cache/` grew one file per source edit forever, no index guard | Superseded builds pruned; `index.php` written on create | review |
| Docblock claimed hits were served "before templates even load" | Corrected to describe what `template_redirect` priority 1 actually saves | — |

## Theme — structured data

| Was | Now | Verified |
|-----|-----|----------|
| Locality, region, opening hours and attorney names hardcoded in the graph | Derived from the existing Customizer fields via `inc/schema-identity.php` | executed — changing `firm_hours` to "Tuesday to Saturday, 10am - 6pm" and the city to Nashville moves the structured data with it |
| Hours could not be edited at all | Parsed from the human-readable line, and **omitted entirely when unparseable** rather than guessed | executed — 7 input shapes |
| Search pages published the front page's exact `@id` | No WebPage entity for search, paginated or archive requests | executed |
| Graph emitted on 404s | Whole graph suppressed on 404, feed, robots | executed |
| Absolute practice URLs became `https://site/https://other/` | `brooks_law_resolve_url()` passes absolutes through, resolves relatives, normalises protocol-relative | executed |
| `JSON_UNESCAPED_SLASHES` disabled the `\/` escaping that keeps `</script>` inert | `JSON_HEX_TAG` added | executed — `</script>` in a title yields one `<script` tag and valid JSON |

## Theme — assets, tokens, packaging

- Editorial artwork, its body class and its stylesheet share **one** filterable
  predicate (`brooks_law_editorial_sky_active`), so markup and CSS cannot diverge.
- `brooks_law_sa_icons()` static-cached — 59 `__()` lookups per call, ~20× per homepage render.
- **Fonts self-hosted.** Fraunces + IBM Plex Sans/Mono, Latin + Latin-Ext, 8 files / 284 KB,
  regenerable with `tools/fetch-fonts.sh`. No request to Google remains in either codebase.
- `editorial-pages.css` de-minified (1 line → 901) and every custom property namespaced
  `--ed-*`. It had declared `--paper` and `--ink`, shadowing the global tokens for
  everything nested inside an editorial page.
- `theme.json` palette and font families fed from Design Studio at runtime via
  `wp_theme_json_data_theme`, so editor swatches match the live site.
- Stylesheet header rewritten: one version (5.3.0), a real description, `Domain Path`.
  Text domain `brooks-law-30-pro` now matches the folder slug.
- `languages/brooks-law-30-pro.pot` generated — 468 strings, all 22 `sprintf` calls carry
  translator notes. `tools/make-pot.py` regenerates it.
- Duplicate `brooks-law-atmosphere` registration collapsed to one function, two callers.
- Loose `true == $checked` → `wp_validate_boolean`; protocol-relative URLs rejected by the
  path sanitiser; `blogname` setting null-guarded.
- Added: cross-document view transitions (reduced-motion gated), `content-visibility` on
  content bands, `:target` scroll-margin.

## Plugin — Docket Suite 5.2.3

- Takeover **always** requires the `brooks_ess_defaults` symbol. The folder-name fast path
  could silently deactivate any plugin whose path contained `site-essentials`.
- `/llms.txt` sends its own `X-Content-Type-Options: nosniff` — it answers on `init` and
  exits, so the theme's header filter structurally cannot reach it.
- 103 translation calls moved from the orphaned `brooks-essentials` domain to
  `docket-suite`. The admin page slug was correctly left alone.
- `languages/docket-suite.pot` generated (109 strings).

## The cross-codebase gap

XML-RPC was live on a default install of both: the theme asserted in prose that the
plugin disabled it and shipped no control, while the plugin's `disable_xmlrpc` default
is `false`.

Resolved in code rather than in a comment. The theme has a Site Tuning toggle (default
on) that calls `brooks_law_plugin_handles_xmlrpc()` and stands down only when the plugin
is *actually configured* to act — so exactly one of the two disables it, and neither can
be wrong about the other.

## Verification

- 56 PHP files, `php -l` clean on PHP 8.4.19.
- Minifiers executed against every shipped stylesheet: brace balance, quote balance and
  `url()` count preserved on all ten. Total CSS 124.8 KB → 78.7 KB.
- Schema graph executed against a WordPress stub across front page, search, 404, single
  page, edited-settings and hostile-title cases.
- Not installed on WordPress. Runtime behaviour under real plugin load, the Customizer
  UI, and block-editor rendering remain unverified.

---

# Validated against the live export (687 items, 170 published pages)

The WordPress export was checked against the shipped code by running the real
functions over the real content. Two defects that only live content could
reveal were found and fixed.

## Fixed after export review

**Duplicate FAQPage markup on 49 pages.** 80 published pages carry hand-pasted
FAQPage JSON-LD in their content; `brooks_law_extract_faqs()` independently
finds two or more Q/A pairs on 72; the two sets overlap on 49. Those 49 pages
were publishing two FAQPage entities describing the same questions.
`brooks_law_add_faq_schema()` now stands down when the content already
declares a FAQPage — the hand-written block wins because it is the one an
editor can see and correct. Verified: 49 stand-downs, 23 pages where the
extractor still adds value, zero duplicates.

**The page-type heuristic scored 0/3 on real slugs, and missed both real
profiles.** Matching "about" or "attorney" anywhere in the slug typed
`germantown-dui-attorney`, `collierville-dui-attorney` and
`bartlett-dui-attorney` as `AboutPage` — three DUI location pages — while
`patrick-brooks-profile` and `beth-brooks-profile`, the two genuine attorney
profiles, fell through as plain `WebPage`. A profile is now recognised by the
profile *layout* (`pb-sec` / `pb-portrait`) and linked to its `Person` entity
via `mainEntity`; slug matches are anchored to the start. Result across 170
pages: 2 `ProfilePage` (both correctly anchored), 1 `ContactPage`, 167
`WebPage`, no false positives.

## Confirmed working against real content

| Check | Result |
|---|---|
| HTML minifier over all 170 pages (2.3 MB) | 0 tag-inventory differences, 0 protected-block corruption, 12% smaller |
| `inc/schema-repair.php` | 83 inline JSON-LD blocks; 15 broken by `<br>` injection, **15/15 repaired**, 0 left broken |
| Component-loader conditional loading | only 2 of 170 pages need component CSS — and both are caught **only** by the `pb-` marker added in 5.3 |
| Editorial layout detection | 7 pages carry `class="blfE`, 7 carry their own `class="sky"` (artwork correctly suppressed) |
| V1 placeholder forgery | 0 pages contain `<!--BLFPROTECT` |
| Nav menu | 53 items, all `post_type` — no custom URLs to go stale |

## Observations worth acting on separately

- **1,415 absolute internal links** in page content (`https://patrickbrookslaw.com/...`)
  against 2 root-relative ones. A staging copy will link back to production.
- **323 raster images** in the library (271 JPG, 36 PNG, 16 JPEG) against only
  22 WebP — the optimiser converts on upload, so the existing library is a
  backlog. Bulk-optimise, and see R6 re: AVIF.
- The uploaded zips unpack to `brooks-law-30-pro-5/` and
  `docket-suite-pro-5-2-2/`. **Installing from those folder names would orphan
  every Customizer setting** — the theme slug must stay `brooks-law-30-pro`.
  The packages in `dist/` use the correct folder names.

---

# Handoff safety (found while checking the upgrade path)

## A "cannot redeclare" fatal when two copies coexist

Simulating the upload — the installed 5.2.2 plugin and this build loaded in the
same request — produced a **white screen**:

```
PHP Fatal error: Cannot redeclare function docket_suite_conflicting_plugins()
```

The dormancy guard was structurally unable to prevent it. PHP early-binds
unconditional top-level function declarations when a file is compiled, before
any statement in it runs, so the `return` at line 216 could not stop the four
`docket_suite_*` functions declared at lines 95–196 from being bound. Confirmed
directly: a top-level `return` above a declaration does not prevent it; a
`function_exists()`-wrapped declaration binds at runtime and survives a double
include.

`includes/core.php` has always documented this exact rule for the shared
`brooks_ess_*` modules — *"PHP binds top-level function declarations at compile
time, so shared functions must live in a runtime-required include, never in the
main plugin file"* — and the bootstrap helpers broke it. The guard only checked
for `brooks_ess_*` symbols, which correctly live in a runtime include and so
were absent; it never got the chance to matter.

**Two fixes, because one alone is not enough:**

1. All eight bootstrap functions moved to `includes/bootstrap.php`, behind a
   `function_exists()` return in the main file. The main file now contains zero
   top-level declarations.
2. They are renamed to a `docket_suite_boot_` prefix. This is load-bearing and
   not cosmetic: the *installed* 5.2.2 file cannot be patched from here, and it
   declares the old `docket_suite_*` names unconditionally. Not sharing those
   names is the only way this build can be loaded alongside one without a fatal.

Verified in both load orders, zero fatals:

| Load order | Before | After |
|---|---|---|
| old 5.2.2 folder first | no fatal (old wins silently) | no fatal |
| new folder first | **FATAL — white screen** | no fatal |

Duplicate resolution is now by **version**, not load order — load order is a
string sort of folder names, which would let an older build in a
lower-sorting folder defeat a newer one. A strictly newer copy causes this one
to retire itself; otherwise this one retires the older copies.

## Theme settings survive a folder-name change

WordPress keys Customizer settings to `theme_mods_<folder name>`, and the
migration only listed three predecessors by name — so activating from a zip
whose folder differed from the installed one silently started with an empty
Customizer. The two `after_switch_theme` migrations (which raced each other on
the same hook) are replaced by one that searches the options table for any
`theme_mods_brooks-law*` sibling and takes the richest.

Verified: settings carry from `brooks-law-30-pro-5`, from an arbitrary folder
name, and from several old copies (richest wins); an existing settings set is
never clobbered; the source row is left intact as a rollback.

**Plugin settings were never at risk** — `brooks_ess_options`,
`brooks_ess_404_log` and `brooks_llms_txt` are folder-independent option keys.

---

# 5.3.1 — CSS minifier rewritten (regression fix)

**5.3.0 broke the live site.** Pages rendered white with no colours, and a large
blue shape appeared mid-page. Both symptoms had one cause.

The Customizer preview skips minification (`is_customize_preview()` returns
early in both minifiers), which is why the site looked correct where it was
being edited and wrong to every visitor.

## What happened

5.3.0 protected string literals *before* stripping comments. `style.css` line 20
contains the comment `Palette drawn from the firm's world`. That apostrophe
opened a string literal which ran to the next apostrophe on line 378 — a
**15,131-byte "string"** containing the entire `:root` block. Rule count fell
from 317 to 120; every design token vanished, so every `var(--court)`,
`var(--paper)`, `var(--ink)` resolved to nothing.

`editorial.css` lost 5 rules the same way, including the sizing and `opacity:.05`
on `.blf-sky`. Its `.blf-sky .water path { fill: #5F86A0 }` then rendered
full-size and fully opaque — the blue shape.

## The fix

Rewritten as a single left-to-right tokenizer. Comments and string literals are
resolved in one pass, so neither can be mistaken for the other. Two further
defects were found and fixed while verifying it — **both of which also existed
in the original 5.2.3 minifier**:

| Defect | Effect |
|---|---|
| `\s*([{};:,>+~])\s*` collapsed around `+` | `calc(64px + env(safe-area-inset-bottom))` became `calc(64px+env(...))`, invalid CSS — the mobile sticky-bar padding |
| `:` squeezed wherever brace depth > 0 | `.statement :where(h2)` became `.statement:where(h2)` — a descendant combinator turning into a compound selector, matching nothing. Depth is a bad proxy for "in declarations": nested at-rules (`@supports { @container { … } }`) are at depth 2 while still reading selectors. Replaced with a stack recording what kind of block each brace opened. |

Whitespace is now removed only around `{`, `}`, `;`, and around `:` inside a
real declaration block. Never around `+ - > ~`, never in a selector, never in an
at-rule prelude. The saving from squeezing combinators is a few hundred bytes;
the cost of getting it wrong is the whole stylesheet.

## Verification

- **`tests/test-minifiers.php`** — 31 assertions, no WordPress required, run with
  `php tests/test-minifiers.php`. Every case is something that actually broke or
  is one character from breaking. This is the regression guard that should have
  existed before 5.3.0 shipped.
- All 9 stylesheets parsed before and after and compared rule-by-rule and
  declaration-by-declaration: **semantically identical**, 118,535 → 85,715 bytes
  (28% saved).
- Spot-checked: `:root` present, `--court` / `--paper` / `--brass-btn` present,
  `calc()` spacing intact, `.blf-sky` rules present.
- HTML minifier re-run over all 170 real pages: 0 differences.
- All 8 scripts re-checked; 58 PHP files lint clean on 8.4.

Version bumped to 5.3.1 so the content hash in `/uploads/brooks-law-cache/`
changes and the broken 5.3.0 files cannot be reused. Superseded builds are
pruned automatically.

**Immediate mitigation if a build ever misbehaves again:** Customizer →
Brooks Law Firm → Performance → untick *Minify theme CSS & JS*. The original
stylesheets are served and the site is restored without touching any files.

---

# 5.3.2 — Yoast schema collision

Checking the export showed **Yoast SEO is live** (173 posts carry
`_yoast_wpseo_title` and `_yoast_wpseo_metadesc`). That matters, because Yoast
emits its own `@graph`, and its entity IDs are:

```
{home}/#website          WebSite
{permalink}#webpage      WebPage
{permalink}#breadcrumb   BreadcrumbList
```

The theme was publishing `{home}/#website` and `{permalink}#webpage` — **the same
two @ids, with different node content, on every page**. Two nodes sharing one
@id is exactly the ambiguity a knowledge graph cannot resolve, and it is the
failure this file's own docblock claimed to avoid. It claimed it in prose and
never checked. Same shape as the XML-RPC gap.

`brooks_law_schema_engine_active()` now detects Yoast, Rank Math, AIOSEO and
SEOPress at runtime. When one is present the theme emits only what that engine
does not — the firm as a `LegalService`/`Attorney` with its hours, service area
and practice catalogue, plus its attorneys as `Person` entities. `WebSite`,
`WebPage` and `BreadcrumbList` are left to the engine that already writes them.

Verified both ways:

| | No SEO plugin | Yoast active |
|---|---|---|
| `#firm` (LegalService) | emitted | emitted |
| `#attorney-*` (Person) | emitted | emitted |
| `#website` | emitted | **stands down** |
| `#webpage` | emitted | **stands down** |
| `#breadcrumbs` | emitted | **stands down** |
| collides with a Yoast @id | — | **no** |

Present in 5.2.3 and live on the site now; not a regression introduced by this
work.

## Ownership map (verified, not assumed)

Zero function-name collisions between theme 5.3.2 (207 functions) and the
installed Docket Suite 5.2.2 (104). The only cross-call is `brooks_ess_get()`,
which exists in 5.2.2 and is `function_exists`-guarded.

| Concern | Owner |
|---|---|
| `<title>`, meta description | **Yoast** |
| Canonical, Open Graph, Twitter | **Yoast** |
| XML sitemap | **Yoast** |
| robots.txt | **Docket Suite** |
| /llms.txt | **Docket Suite** |
| 301 redirects, 404 log | **Docket Suite** |
| JSON-LD firm + attorney entities | **Theme** |
| JSON-LD WebSite / WebPage / Breadcrumbs | **Yoast** (theme stands down) |

Uploading the theme alone therefore changes the structured-data layer and
nothing else.

---

# 5.3.3 — settings migration picked the wrong source row

On the live migration the text settings carried over but the **hero photo and
the header/footer ribbon photos did not**.

Not a WordPress quirk — a weakness in the 5.3.0 migration. It chose between
sibling `theme_mods_brooks-law*` rows with this rule:

```php
// The richest set of saved values is the one that was actually in use.
$size = count( $source );
```

That is a guess, and on a site carrying five such rows it guessed wrong: an
older row held more keys and won, and it predated those images being set.

WordPress already records the answer. `switch_theme()` writes the outgoing
slug to the **`theme_switched`** option before `after_switch_theme` fires, so
the previous theme is knowable rather than guessable. It is now the first
candidate and wins outright.

A second layer closes the case where that option is unavailable: after the
primary row is chosen, any key it does **not** carry is filled from the other
siblings, richest first. Existing values are never overwritten, so the previous
theme always wins where it has an opinion; this only recovers keys it has none
for.

The trade-off, stated in the code: a setting deliberately cleared on the
previous theme but still present on an older one comes back. On a
first-activation-only pass into an empty slate, resurrecting a stale value is a
far smaller harm than silently losing a current one.

Verified against a reconstruction of the live site — five sibling rows, the
active one deliberately *not* the richest:

| Scenario | hero_image, ribbons, logo, menus |
|---|---|
| `theme_switched` present (what WordPress does) | all present |
| `theme_switched` absent, gap-fill only | all present |
| target already has settings | untouched, migration correctly does nothing |
| source rows after migrating | unchanged, still available as rollback |

**No action needed if the photos have already been re-set by hand** — this is
insurance for the next theme change, not a fix to re-apply.

---

# Docket Suite 5.2.4 — search-engine verification tags

Yoast has been removed from the site in favour of Docket Suite's own SEO half.
That lost the one place a Bing or Google verification code could be entered
without touching the filesystem — Docket Suite had no such field (verified: zero
matches for any verification setting in the codebase).

`includes/verification.php` adds one: Bing, Google Search Console, Yandex and
Pinterest, entered under **Settings → Site Essentials → Search-engine
verification**, printed as meta tags in the head.

Two decisions worth recording.

**It does not stand down for other SEO plugins.** `seo.php` correctly stands
down when Yoast or Rank Math is active, because titles and canonicals genuinely
conflict. Verification tags do not — a duplicate is redundant but harmless, and
an empty field emits nothing. Standing down here would mean losing verification
the moment another plugin is switched off, which is precisely how this site lost
its Bing field in the first place.

**The field accepts the whole `<meta>` tag, not just the token.** Every one of
these services displays the complete tag on screen, so the complete tag is what
gets pasted. Accepting both is two lines and removes the most likely way this
setting gets entered wrong — a field silently holding markup and emitting a tag
inside a tag. Verified against eight paste shapes including single-quoted
attributes, smart quotes, and an embedded `<script>`:

```
whole Bing tag        -> A1B2C3D4E5F6A1B2C3D4E5F6
single-quoted attr    -> TOKEN123
smart-quote paste     -> A1B2C3D4
script injection try  -> x
empty                 -> (emits nothing)
```

## Related: the Yoast meta migration has not been run

`seo.php` reads Yoast's own meta keys at render time when its fields are empty
(lines 143, 159, 182), so the 173 hand-written titles and meta descriptions kept
working when Yoast was deactivated. That fallback holds only while Yoast's post
meta remains in the database — **deactivating Yoast preserves it, deleting Yoast
removes it.**

The one-shot migration on the Docket SEO settings screen copies those values
into `_docket_seo_*` keys. It never overwrites an existing value, skips Yoast
template patterns such as `%%title%%`, and writes a per-post backup so a single
Undo restores the previous state. It should be run before Yoast is deleted.
