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
