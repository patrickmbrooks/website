# Docket Suite Pro 5.2.2 × Brooks Law 5.2.3 — Integration Review

Companion to `brooks-law-30-pro-5.2.3-code-review.md`. Static review of the
20 plugin files against the 57 theme files, plus hook-ordering analysis.

Published version: https://claude.ai/code/artifact/e1f70847-eb64-48d9-ac2f-1abc92dac0ae

## Verdict

A genuinely deliberate integration, and most of it works. 285 functions
across both codebases, zero name collisions. The ownership split is
documented and honoured — `seo.php` contains zero JSON-LD (verified by
grep), so there is no second LegalService entity. Robots.txt has one
writer because a prior collision was found, fixed and written down.

The failures are gaps, not collisions. Each codebase reasons about the
other in prose comments rather than in code, and where one asserts "the
other side handles this," nothing verifies it.

## The request ladder

| Hook | Owner | What |
|------|-------|------|
| `init:0` | Plugin | `brooks_llms_maybe_serve` — /llms.txt, text/plain, exits |
| `template_redirect:0` | Plugin | `brooks_ess_early_redirect` — exact-match 301, live-page guarded |
| `template_redirect:0` | Plugin | `maybe_render_sitemap`, `legacy_sitemap_redirects` — XML, exits |
| `template_redirect:0` | Plugin | `docket_indexnow_serve_key_file` — text/plain, exits |
| `template_redirect:1` | Plugin | `brooks_ess_maybe_redirect`, `attachment_redirect` — 404-gated |
| `template_redirect:1` | Theme | `brooks_law_cache_start` — cache HIT, or opens buffer #1 |
| `template_redirect:2` | Theme | `brooks_law_html_min_start` — opens buffer #2 |

Every non-HTML plugin response exits before either theme buffer opens, so
the theme's HTML minifier cannot mangle Markdown or XML bodies. At
priority 1 the plugin registers first (plugins load before `functions.php`;
same-priority hooks run in registration order), so a 301 always beats a
cache hit. Not luck — `early-redirects.php:149` documents the choice.

## Seam findings

- **X1 (High)** — XML-RPC enabled because each side deferred to the other.
  `theme/inc/site-tuning.php:7` states as fact "Brooks Essentials already
  disables XML-RPC…" and ships no toggle on that basis.
  `plugin/includes/core.php:41` has `'disable_xmlrpc' => false`. The
  comments half of the same sentence is true (`disable_comments => true`),
  which is what makes the false half easy to miss. Default install of both:
  XML-RPC live, nobody handling it.
- **X2 (High)** — `plugin/includes/crawlers.php:318` calls
  `brooks_law_cache_purge()` on every settings save, inheriting the theme's
  `wp_cache_flush()` (theme finding I4). Saving a plugin setting now takes
  the whole object cache cold. Also a coupling risk: the plugin calls theme
  internals guarded only by `function_exists()`.
- **X3 (Medium)** — Eight firm literals (phone, phone link, cell, cell link,
  email, address, city/state, hours) are byte-for-byte identical in
  `brooks_law_defaults()` and `brooks_ess_defaults()` — verified. Resolution
  is theme mod → theme default → plugin default, so plugin copies are inert
  now and a stale snapshot after a theme switch. The third copy — the
  theme's `schema-graph.php` hardcodes — is editable from nowhere.
- **X4 (Medium)** — `docket_suite_conflicting_plugins()` documents
  "Detection is by symbol, not by folder name," but its fast path matches
  the plugin *path* against `brooks-law-essentials`, `brooks-essentials`
  and `site-essentials`, then `continue 2` past the symbol check. The last
  needle is generic enough to hit a third-party plugin, which would then be
  silently deactivated on the next admin page load.
- **X5 (Medium)** — `Docket_SEO::filter_title` (`pre_get_document_title:15`)
  feeds the theme's `wp_get_document_title()` call at `wp_head:20`, so SEO
  titles become the schema `name` for free. Nice emergent benefit — but it
  guarantees the theme's `@id` collision (I2) now produces two entities with
  the same `@id` and visibly different names.
- **X6 (Low)** — The theme's `nosniff` header reaches the plugin's sitemap
  and IndexNow responses (both after `WP::send_headers()`) but not
  `/llms.txt`, which exits at `init:0`. Admin-authored content, and modern
  browsers don't sniff `text/plain` into HTML, so hardening not a hole —
  but the theme's header structurally cannot reach it, so add one there.
- **X7 (Low)** — 100 translation calls pass `'brooks-essentials'`, 10 pass
  `'docket-suite'`, header declares `docket-suite`. The option-key mismatch
  is deliberate and documented (it's what carries settings over from
  Essentials); the text-domain one is not.

## What the integration gets right

- Schema ownership split cleanly and actually honoured — `seo.php` has no
  JSON-LD at all.
- Robots.txt has exactly one writer; the earlier two-writer collision is
  documented in the plugin header and `crawlers.php` reads
  `DOCKET_SUITE_SEO_ACTIVE` to advertise the right sitemap.
- Redirect layering cannot shadow a live page: priority-0 exact match calls
  `brooks_ess_path_is_live_page()` first, priority-1 fallback is
  `is_404()`-gated, `do_redirect_guess_404_permalink` disabled,
  `wp_safe_redirect` throughout.
- `spc-watchdog.php` fires an admin notice when the theme's page cache is on
  alongside Super Page Cache, naming the exact Customizer path.
- The plugin's settings screen explains the duplicated firm-info UI in place.
- `llms-auto.php` reads `post_content` raw rather than running
  `the_content`, so no theme content filters run during generation.
- Zero name collisions across 285 functions. Both AJAX handlers check nonce
  and `manage_options`. All 20 files pass `php -l` on PHP 8.4.

## Combined fix order

1. Delete the theme's `wp_cache_flush()` — now two trigger paths (X2, I4).
2. Close the XML-RPC gap and fix the comment that caused it (X1).
3. Fix the three structured-data defects (X3, X5, I1–I3).
4. Remove `site-essentials` from the takeover fast path (X4).
5. Replace or default-off the theme's minifiers (V1–V3).
6. Give the theme↔plugin coupling a real contract (X2).
7. Reconcile both products' identities — keep the option keys (X7, I12).

## Scope

Not installed. The ladder is reasoned from registration order and
WordPress's documented load sequence, not observed running. Yoast/Rank
Math/AIOSEO stand-down, the SPC watchdog against a real install, WebP
conversion, and the chunked llms.txt save were read but not exercised.
