# Brooks Law 30 Pro v5.2.3 — Theme Code Review

Static review of the 57 shipped files, plus direct execution of the two
minifiers and the defaults builder in isolation on PHP 8.4.

Published version: https://claude.ai/code/artifact/29cba120-4f5f-4b07-8e9d-2b8a0c6f1b4e

## Verdict

The foundation is better than most commissioned WordPress work. Security
hygiene is genuinely careful, the CSS shows real platform awareness, and
181 functions across 57 files share one prefix with zero collisions.

The weaknesses concentrate in the features added last — the performance
layer and the schema graph — where the code is more ambitious than it is
careful. The recurring pattern is a gap between what the docblocks claim
and what the code does. None of it needs rework; it needs about a day of
corrections, and the docs need to stop overselling.

## Reproduced defects (executed, not inferred)

| ID | Finding | Severity | Location |
|----|---------|----------|----------|
| V1 | HTML minifier placeholder token can be forged from page content | Medium | `inc/performance.php:186-206` |
| V2 | HTML minifier rewrites attribute values, not just inter-tag whitespace | Low | `inc/performance.php:203` |
| V3 | CSS minifier does not skip quoted strings | Low | `inc/performance.php:243-250` |

**V1** — Protected `<pre|textarea|script|style>` blocks are swapped for a fixed
placeholder `<!--BLFPROTECT0-->` then restored with `strtr()`. Nothing checks
whether the document already contained one.

```
in  <div><!--BLFPROTECT0--></div><script>1</script>
out <div><script>1</script></div><script>1</script>
```

Same-page content substitution only, so a correctness bug rather than an
injection vector — but HTML minify is on by default, the docblock calls the
routine "safe", and the fix is a per-request random token suffix.

**V2** — `preg_replace('/\s{2,}/', ' ', $html)` runs across the whole document.

```
in  <img alt="a  b" src="x">
out <img alt="a b" src="x">
```

**V3** — Whitespace-stripping around `{};:,>+~` has no notion of string bounds.

```
in  .a::after{content:"Note: one, two"}
out .a::after{content:"Note:one,two"}
```

Does **not** currently corrupt this theme — `style.css` and `editorial.css`
both round-trip cleanly. But `assets/css/editorial.css:134` already ships
`content: "Portrait \A 1200 \00D7 800 px"`, where the spaces after `\A` and
`\00D7` are CSS escape terminators. One edit from the failure mode.

## Findings by inspection

### Structured data
- **I1 (High)** — `inc/schema-graph.php:71-108,183-200`. Address locality,
  region, `areaServed`, opening hours (08:00–17:30 M–F) and both attorney
  names are hardcoded, while `firm_city_state` and `firm_hours` are
  Customizer fields the visible page reads. Change the hours and the site
  and its structured data disagree silently.
- **I2 (Medium)** — `inc/schema-graph.php:298-310`. `brooks_law_current_url()`
  falls through to `home_url(trailingslashit($wp->request))`; on a search page
  `$wp->request` is empty, so the WebPage entity gets the front page's exact
  `@id` with a different `name`. No 404 guard either.
- **I3 (Medium)** — `inc/schema-graph.php:172,213`. Practice-area URLs are
  wrapped in `home_url()` unconditionally, but the Customizer explicitly
  permits absolute URLs. Yields `https://site.com/https://other.com/` in
  schema only; the rendered page is correct.

### Performance layer
- **I4 (High)** — `inc/performance.php:224`. `wp_cache_flush()` on every
  `save_post`, menu save, Customizer save, and plugin activate/deactivate.
  On a persistent object cache this discards the entire store, not just this
  theme's. The `$wpdb` delete above it already does the job.
- **I5 (Medium)** — Page cache documented as running "before templates even
  load"; it hooks `template_redirect` priority 1, after full bootstrap and the
  main query. Also missing: `Vary`/`Cache-Control`, `DONOTCACHEPAGE`, cleanup
  of `/uploads/brooks-law-cache/`, and an `index.php` guard there.
- **I6 (Medium)** — `inc/component-loader.php` gates the editorial component
  CSS, but `inc/editorial-sky.php:24` enqueues `editorial.css` (11.3KB) and
  `editorial.js` unconditionally, and adds the `brooks-editorial` body class
  to every page.
- **I7 (Low)** — `brooks_law_sa_icons()` builds a 59-entry array of live `__()`
  calls (so PHP cannot intern it), invoked ~20× per homepage render including
  inside a `foreach`. One `static` fixes it. Note: `brooks_law_defaults()`
  looks like the same problem and is **not** — benchmarked at 0.01ms for 200
  calls, because PHP interns constant array literals.

### Design system integrity
- **I8 (Medium)** — `assets/css/editorial-pages.css:16` redefines `--paper`
  and `--ink` scoped to `.blfE`, shadowing the global theme tokens for
  everything nested inside, and neutering those Design Studio controls there.
- **I9 (Medium)** — `theme.json` hardcodes the palette Design Studio overrides
  at runtime; `editorial.css` carries 52 raw hex literals and
  `editorial-pages.css` another 46.
- **I10 (Low)** — `editorial-pages.css` ships pre-minified as one 15KB line.
  No reviewable source; also why I8 went unnoticed.

### Third-party and packaging
- **I11 (Medium)** — Google Fonts (Fraunces, IBM Plex) load from
  `fonts.googleapis.com` on editorial pages, contradicting the theme's
  zero-webfont posture. Render-blocking third party, plus visitor IPs to
  Google — adverse EU case law (LG München I, 3 O 17493/20).
- **I12 (Medium)** — Three version numbers in the stylesheet header
  ("Brooks Law 4.0 Pro" / `Version: 5.2.3` / description opening "Version 3.0
  Pro"); a 2,400-char changelog as the `Description:`; text domain
  `brooks-law` ≠ slug `brooks-law-30-pro`; no `/languages`, POT, or
  `Domain Path` for 492 wrapped strings; 11 of 22 `sprintf(__())` lack
  translators comments; every default is the firm's real contact data.
  `screenshot.png` is correctly 1200×900.
- **I13 (Low)** — Loose `true == $checked` in `brooks_law_sanitize_checkbox()`
  vs `wp_validate_boolean` elsewhere; `brooks_law_sanitize_url_or_path()`
  admits protocol-relative `//evil.com`; unguarded
  `get_setting('blogname')->transport`; empty `_br_ribbon` meta row written on
  every page save; `wp_enqueue_media()` on every post-edit screen; duplicate
  `brooks-law-atmosphere` handle registration and `brooks-law-editorial` used
  for both a style and a script. Also `JSON_UNESCAPED_SLASHES` in
  `brooks_law_output_graph()` disables the `\/` escaping that makes a stray
  `</script>` in JSON-LD harmless — no working exploit found (KSES strips
  stray closing tags for non-`unfiltered_html` users), so defense in depth,
  but the flag buys nothing.

## What holds up

- **Security discipline.** All three metabox savers run nonce → autosave
  guard → `current_user_can('edit_post', $post_id)` → sanitize + unslash, in
  that order. Every Customizer setting has a `sanitize_callback`. Icon keys
  are re-validated at read time as well as save time, which is why the
  unguarded `$icons[$key]['svg']` access in `front-page.php` cannot fault.
  No `$_REQUEST`, no `eval`, no `unserialize`. Each of the 19 `phpcs:ignore`
  comments names a specific justification.
- **CSS platform awareness.** Hex first with OKLCH behind `@supports`; a
  second `@supports` gate for relative color syntax with a comment explaining
  it shipped later than `oklch()`; `prefers-contrast: more`;
  `text-wrap: balance`/`pretty` through `:where()` at zero specificity.
- **JavaScript is correct progressive enhancement.** Every file bails when its
  target is absent; scroll handlers rAF-throttled and `{passive: true}`;
  reduced motion respected; `navigation.js` injects real `<button>` submenu
  toggles with `aria-expanded`, sibling close, Escape and outside-click.
- **Namespacing.** 181 functions, one prefix, zero collisions, nothing global.
  All 57 files pass `php -l` on PHP 8.4.

## Fix order

1. Delete the `wp_cache_flush()` call (I4) — one line, largest production win.
2. Fix the three schema defects (I1, I2, I3).
3. Reconcile docblocks with behaviour (I5, I6).
4. Replace or default-off the hand-rolled minifiers (V1, V2, V3).
5. Self-host Fraunces and IBM Plex (I11).
6. Rewrite the stylesheet header and neutralise defaults (I12) — if selling.
7. Unify the tokens (I8, I9, I10) — largest job, makes Design Studio true.

## Scope

Runtime behaviour is unverified: page cache under concurrency, the Customizer
UI, block-editor rendering, and interaction with the Brooks Law Essentials
plugin the code repeatedly defers to. Only V1–V3 were observed rather than
read.
