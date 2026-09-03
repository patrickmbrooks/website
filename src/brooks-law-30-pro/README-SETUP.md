# Brooks Law 3.0 Pro — Install & Setup

Installs SIDE-BY-SIDE with your current theme (new slug), and on first
activation automatically copies all your existing Customizer settings, logo,
and menu locations from 2.4 / 2.3 / the original — nothing to re-enter.
Switching back to the old theme at any time restores everything instantly.

## Install
1. Appearance → Themes → Add New → Upload Theme → `brooks-law-30-pro.zip`
2. Activate. (Do it on staging first if you have one.)
3. Hard-refresh the site.

## What's new in 3.0

### 1. Design Studio (Customize → Brooks Law Firm → Design Studio)
Full site restyling without code: all 13 palette colors, heading + body
font pairing (7 local stacks — zero download, zero layout shift), base text
size, heading scale, line height, section spacing density, corner radius,
reading-column width, button style (solid / outline / pill). Only changed
values are output, as CSS variables — the shipped design stays canonical.

### 2. Performance (Customize → Brooks Law Firm → Performance)
- **Page cache** — full-HTML cache for anonymous visitors, purged
  automatically the instant you save any post, page, menu, or Customizer
  change. OFF by default: turn it on once the site looks right.
  ⚠ If you run WP Rocket / LiteSpeed / W3TC, leave this OFF — one page
  cache only, ever.
- **Asset minify** (ON) — theme CSS/JS minified to hashed immutable files
  in /uploads/brooks-law-cache/, rebuilt automatically when sources change.
- **HTML minify** (ON) — whitespace/comments stripped from output;
  pre/textarea/script/style protected.
- **Hero preload** (ON) — LCP image preloaded with srcset for faster
  first paint.

### 3. Knowledge graph schema (Customize → … → SEO & Schema)
One connected @graph replaces the old single block: LegalService firm
entity ⇄ Patrick & Beth as Person entities ⇄ WebSite (with sitelinks
SearchAction) ⇄ per-page WebPage/AboutPage/ContactPage with LIVE
datePublished/dateModified ⇄ BreadcrumbList. Practice areas feed in as an
OfferCatalog from the same Customizer values that render the homepage.
New fields: paste your Google Business Profile / Avvo / Justia / LinkedIn
URLs (sameAs) and each attorney's profile path + law school — the biggest
authority wins are those sameAs links, so fill them in.
Everything updates itself when content changes (dynamic sync); validate at
https://validator.schema.org and Google's Rich Results Test after install.

### 4. Visual page building (block editor, no plugin)
- theme.json switches on the FULL core styling toolset in the editor —
  firm palette, font presets, fluid type sizes, spacing scale, borders,
  radii, shadows, duotone, wide/full-width layouts.
- Editor canvas styled to match the front end exactly.
- One-click patterns under the "Brooks Law Firm" category: CTA band,
  practice-area card grid, attorney profile, FAQ, consultation banner —
  insert, edit the text, publish.

## Still assumed installed
Yoast (page titles/meta — the graph complements it, never duplicates it)
and your contact form plugin.

---

## 3.1.0 — Text / call toggle

**Install as a replacement, not a new theme.** Appearance → Themes → Add New →
Upload Theme → choose the zip → "Replace current with uploaded". The folder is
still `brooks-law-30-pro` on purpose: that name is the theme slug, and changing
it would leave every saved Customizer value behind.

### What changed

| File | Change |
| --- | --- |
| `inc/contact-toggle.php` | New. The toggle, the sticky bar, and their Customizer section. |
| `assets/js/contact-toggle.js` | New. Chip selection and the desktop copy fallback. |
| `inc/template-tags.php` | Old `brooks_law_call_bar()` removed — it lives in the new file now. |
| `front-page.php` | Hero's two stacked call buttons replaced with the toggle. |
| `functions.php` | Requires the new file, enqueues the script, adds defaults, new hero lead copy. |
| `style.css` | Section 15 rewritten; section 15b is the new sticky bar. |
| `footer.php` | Untouched. Still calls `brooks_law_call_bar()`. |

### Where to edit it

Customizer → Brooks Law Firm → **Text / Call Buttons**. Numbers, the question
above the chips, up to three chips with their prefilled messages, both button
labels, and the sticky bar labels. Leave a chip's label blank to hide it.

### Adding the toggle to another template

```php
<?php brooks_law_contact_toggle(); ?>                          // auto-picks the matter
<?php brooks_law_contact_toggle( array( 'matter' => 1 ) ); ?>  // force the 2nd chip
<?php brooks_law_contact_toggle( array( 'context' => 'light', 'chips' => false ) ); ?>
```

On light backgrounds pass `'context' => 'light'`.

### Two filters

```php
// Which URL fragments select which chip on interior pages.
add_filter( 'brooks_law_matter_path_map', function ( $map ) {
    $map['m2'][] = 'commercial-drivers-license';
    return $map;
} );

// Hide the sticky bar on a page.
add_filter( 'brooks_law_show_call_bar', function ( $show ) {
    return is_page( 'contact' ) ? false : $show;
} );
```

### Rolling back

Re-upload the 3.0.1 zip the same way. Nothing in the database changes, so the
site returns to the old call bar with every setting intact.

---

## 3.1.1 — Toggle on criminal, traffic, and CDL pages

The toggle now also renders in the page hero, under the H1, on every
criminal, traffic, and CDL page. Which pages those are is a recorded decision,
not string luck: `brooks_law_page_matter_map()` in `inc/contact-toggle.php`
lists all 115 published pages by slug in three buckets.

| Bucket | Pages | Behavior |
| --- | --- | --- |
| `criminal` | 80 | Toggle shows, criminal chip preselected |
| `traffic` | 13 | Toggle shows, traffic/CDL chip preselected |
| `none` | 22 | No toggle. Sticky bar uses the neutral prefill |

`none` covers divorce, civil litigation, personal injury, IP, maritime, music,
wrongful death, the attorney profiles, blog, contact, resources, privacy
policy, and the Spanish immigration resource pages.

Pages added *after* this build fall through to keyword matching
(`brooks_law_guess_matter()`), which recognises traffic terms first, then
criminal terms, and shows nothing if neither matches — the safe direction.

### Changing which bucket a page is in

```php
add_filter( 'brooks_law_page_matter_map', function ( $map ) {
    $map['traffic'][] = 'new-cdl-page';        // add
    $map['none'][]    = 'order-of-protection'; // 'none' wins over the others
    return $map;
} );
```

Buckets are checked `none` → `traffic` → `criminal`, so adding a slug to
`none` suppresses the toggle without editing the original list.

### Turning it off

Customizer → Brooks Law Firm → Text / Call Buttons → **"Also show them on
criminal, traffic, and CDL pages."** Unchecking it leaves the homepage toggle
and the sticky bar exactly as they were in 3.1.0.

---

## 3.2.0 — Email/call toggle for civil pages

Civil pages get their own version of the control: **email Beth Brooks** on the
left, **call the office** on the right. No text option — the criminal text line
is the wrong front door for a divorce or a contract dispute. Both sides are
equal width here, because email and the office line are equally good ways in.

`brooks_law_page_matter_map()` now has four buckets:

| Bucket | Pages | Control |
| --- | --- | --- |
| `criminal` | 80 | Text / Call |
| `traffic` | 13 | Text / Call |
| `civil` | 12 | Email / Call |
| `none` | 10 | No toggle |

Civil covers: divorce (all four), civil litigation, business litigation,
intellectual property, maritime law, music artist representation, personal
injury, wrongful death, and Beth Brooks' profile page.

### Consultation fee

Every civil page shows the fee line under the buttons. Wrongful death and
personal injury show the no-fee line instead. Which slugs are exempt:

```php
add_filter( 'brooks_law_free_consult_slugs', function ( $slugs ) {
    $slugs[] = 'another-contingency-page';
    return $slugs;
} );
```

Both lines are Customizer text — Brooks Law Firm → Text / Call Buttons.

### "Civil & criminal" above the office number

The call side now labels the office line as civil and criminal on the front
page and on every criminal and traffic page. On civil pages it reads "Civil
matters". Both labels are editable.

### Note on the homepage

The hero trust point and top bar still say "Free initial consultation". That is
accurate for criminal and traffic work but not for civil matters, which carry
the hourly fee. Those two fields are Customizer values — worth a look.

---

## 3.2.1 — FAQ schema + theft cluster slugs

**New: `inc/faq-schema.php`.** Any heading that ends in a question mark is read
as a question, and the paragraphs and lists under it become the answer. Two
pairs minimum. It appends to the graph through the existing
`brooks_law_schema_graph` filter, so `inc/schema-graph.php` is untouched.

This is retroactive — it fires on pages you already have. Verified against live
content: `/dui/` yields 8 pairs, `/how-much-does-a-cdl-ticket-lawyer-cost-memphis/`
7, `/how-much-does-a-criminal-defense-lawyer-cost-memphis/` 7. `/theft/` and
`/robbery/` yield none, because neither page currently uses question headings.

Switch: Customizer → SEO & Schema → **FAQ schema**.

To exclude a page, or hand-build its pairs:

```php
add_filter( 'brooks_law_faqs', function ( $faqs, $post ) {
    return ( 'privacy-policy' === $post->post_name ) ? array() : $faqs;
}, 10, 2 );
```

**Seven theft-cluster slugs registered** in the `criminal` bucket of
`brooks_law_page_matter_map()`, so the text/call toggle appears on them with the
criminal chip preselected as soon as the content is imported.

---

## 4.0.0 — Urgent tiles inverted, theme renamed

Display name is now **Brooks Law 4.0 Pro**. The folder is still
`brooks-law-30-pro` — it is the theme slug, and it must never change, or every
saved Customizer value is orphaned. Upload → "Replace current with uploaded"
works exactly as before.

The three urgent Action Center tiles now use the courtroom-slate treatment
(navy card, white title, brass disc) instead of the brass-tinted wash, which
measured 1.01:1 against the limestone band — the urgent tiles were the least
visible ones. They also carry a small text tag ("Time-sensitive", editable or
hideable at Customizer → Action Center → Urgent tile tag) so urgency is stated
in text rather than conveyed by color alone (WCAG 1.4.1).

New token: `--court-soft` for secondary text on dark surfaces.

---

## 4.1.0 — Interior page tile rows

Google usually lands people on an interior page, not the homepage. 4.1 adds a
compact "Helpful right now" row under the hero on criminal-defense pages, plus
Personal Injury and Wrongful Death. Existing page content is untouched — the row
is inserted between the hero and the body.

Tiles are curated per topic, not generic. `/dui/` shows DUI-cluster links,
`/order-of-protection/` shows domestic assault, assault, theft, and burglary,
`/personal-injury/` shows wrongful death and civil litigation. Explicit map in
`brooks_law_tile_map()`; anything unmapped falls back to its contact-toggle
matter bucket (criminal or traffic) and shows nothing otherwise, so other civil
pages, profiles, blog, and privacy stay clean.

A tile pointing at the page it sits on is dropped automatically, and a row with
fewer than two tiles doesn't render. Maximum four per row.

Master switch: Customizer → Action Center → **Show tile rows on interior pages**.
Heading is editable there too.

Filters: `brooks_law_tile_library`, `brooks_law_tile_sets`, `brooks_law_tile_map`,
`brooks_law_page_action_tiles`.

---

## 4.2.0 — Ribbon artwork library

Fifteen original line-art motifs for the page-title ribbon, built on the same
pattern as the 59-icon set: `brooks_law_ribbon_art()` returns a keyed array of
label + side + inline SVG, `brooks_law_sanitize_ribbon_art()` whitelists the key
and returns '' for anything unknown.

The art is original work drawn for this theme — no photos, no stock, nothing to
license. Each motif is drawn on a 480x260 canvas with its visual mass on one
side so the ribbon title always has clear ground opposite. Everything uses
`currentColor` and inherits the ribbon tint.

**Motifs:** Steamboat, River boat, M bridge, Downtown skyline, Bar sign,
Bar sign — Open 2 AM, Bar sign — Live Music, Trolley car, Iron gate with note,
Steel guitar, Blues guitar, Buffalo, Oak tree, Columned house, Pyramid.

**Site-wide:** Customizer → Ribbon Artwork — enable, default motif, default
opacity (8–45, clamped), and drift on scroll.

**Per page:** the Page Ribbon box on the edit screen gains Background artwork
(site default / none / any motif), Artwork side (automatic, left, right), and
Artwork opacity (blank = site default).

Pages that already use a **ribbon photo keep the photo** — art is skipped
entirely when a photo is set, so nothing existing changes.

The drift is `assets/js/ribbon-art.js`: ~1.5KB, deferred, loaded only on
singular views when the option is on, bails immediately for reduced-motion or
when no ribbon on the page carries art. It only sets a transform, so it cannot
affect layout.

Filters: `brooks_law_ribbon_art`, `brooks_law_ribbon_art_resolved`.

Note: if a motif is removed from the library via filter, pages referencing it
fall back to no art rather than to the site default — deliberate, so a removed
motif never silently becomes a different one.

---

## 4.3.0 — Editorial layout as a theme feature

Six pages (both alcohol pages, all three probation-violation pages, and Shelby
County Drug Court) each carried an identical ~14KB `<style>` block plus ~10KB of
inline SVG inside their post content. Same bytes, six times, none cacheable,
none editable in the block editor, and defining eighteen of their own CSS
variables that ignored the Customizer.

That layout is now a theme feature any page can switch on.

- **`assets/css/editorial-pages.css`** — the stylesheet, verbatim, enqueued once
  and cached. Selectors unchanged, so the six existing pages render identically.
- **Fonts** — the `@import` of Google Fonts (render-blocking, no preconnect,
  discovered late) is replaced by a proper enqueue with resource hints. Or set
  Typeface to System and the request disappears entirely.
- **`brooks_law_edpage_scene()`** — the scene SVG lives in the theme, so a new
  page needs a checkbox rather than pasted markup.

**Per page:** Editorial Layout box on the edit screen. Pages that already carry
the layout inline are detected automatically and say so.

**Site-wide:** Customizer → Editorial Layout — enable, and Typeface
(Editorial = Fraunces + IBM Plex, as today; System = Georgia + system sans,
no webfont request).

Kept deliberately separate from `inc/editorial-sky.php`, the older sky-only
layer on `.blf-sky` — different prefix (`brooks_law_edpage_*`), different file,
different stylesheet. Where both would apply, the older sky stands down for that
request so they never stack.

The six inline copies are now redundant but harmless — identical rules. Removing
them is post content, so it needs a one-time patch plugin, not a theme upload.

---

## 4.4.0 — All nine pages consolidated, without touching content

4.3 moved the editorial stylesheet into the theme. 4.4 finishes the job for
every page that carries inline CSS, and does it **without rewriting anyone's
post content**.

Instead of editing the database, the theme simply does not print a block it has
already superseded, and enqueues the cached file instead. Nothing is destroyed,
so unticking one Customizer box puts everything back, and a page opened in the
editor still shows its original markup.

**Coverage — 109.6KB removed from rendered HTML across nine pages:**

| Pages | Block | Replaced by |
| --- | --- | --- |
| 6 editorial pages | 14KB | `assets/css/editorial-pages.css` |
| `cordova-criminal-defense` | 11.9KB | same file — its 116 selectors are a strict subset, identical declarations |
| Both attorney profiles | 8KB | `assets/css/profile-pages.css` (new) |

The profile pages differed only in the pull-quote measure (26ch vs 30ch); that
is now `--pb-quote-measure`, defaulting to 28ch, overridable per page.

**Safety.** Blocks are matched by md5, not by page. Only the four known copies
are ever suppressed. A block added tomorrow, or one of these edited so its hash
changes, prints exactly as before — verified in testing.

Switch: Customizer → Editorial Layout → **Serve shared CSS from the theme**.

No patch plugin is needed for this. `brooks-editorial-cleanup.zip` is superseded
and should not be run.

---

## 4.5.0 — Legacy pages fully unified

The seven pages carrying the editorial layout each held their own copy of the
parallax scene as well as the stylesheet. 4.5 substitutes the theme's copy at
render, so all nine pages now draw from exactly the same code as any page you
opt in from the edit screen. One source of truth.

**This does not make pages smaller.** The scene has to stay inline SVG — the
weather cross-fade is driven by CSS from the parent, and an external file or
`<use>` reference could not be styled that way. The theme injects the same
~10KB. What changes is that editing `brooks_law_edpage_scene()` now updates
every page, instead of seven pages each needing a separate edit.

Matching is by md5 of the exact block, and both halves (`.wx` and `.sky`) must
be recognised before anything is substituted — so a page whose scene has been
edited keeps its own version. Verified: a scene with a single added attribute is
left completely alone.

Switch: Customizer → Editorial Layout → **Serve the scene artwork from the
theme**.

### Where that leaves things

| | Stylesheet | Scene |
| --- | --- | --- |
| 6 editorial pages | theme | theme |
| Cordova | theme | theme |
| 2 attorney profiles | theme | n/a |
| Any page you opt in | theme | theme |

Nothing writes to the database at any point. Every part of this is a render-time
substitution controlled by three Customizer checkboxes, and unticking them
restores the original behaviour exactly.

---

## 4.5.1 — Fix: editorial checkbox on profile pages

Ticking "Use the editorial layout" on an attorney profile page garbled it. The
wrap only recognised `class="blfE"` as an existing layout, so it did not see the
profile system's `.pb-` components, wrapped them in `.blfE`, and editorial
typography overrode the profile styling.

`brooks_law_edpage_has_own_layout()` now recognises both systems, and any page
that has one is never wrapped. The edit screen explains which layout is in use
instead of offering a checkbox that would break the page.

The editorial pages (probation violation, selling alcohol, drug court, Cordova)
were never affected — they carry `class="blfE"`, so the wrap already skipped
them and ticking the box did nothing.

Extend the detection with the `brooks_law_edpage_layout_markers` filter.

---

## 4.6.0 — Layout selector on every page

The checkbox becomes a three-way **Layout** selector, and it now works on the
pages that carry the editorial markup in their own content — previously those
had no control at all.

| Choice | Effect |
| --- | --- |
| Automatic | Editorial if the page's content carries the markup, Standard otherwise. Matches how every page behaves today. |
| Editorial | Forces the editorial layout on. |
| Standard | Forces it off — including on probation violation, selling alcohol, drug court, and Cordova. The stylesheet is not enqueued, the inline block is not printed, and the artwork is dropped, leaving the text in the ordinary site layout. |

Nothing is deleted at any point. Standard is a render-time decision, so the
markup stays in the database and switching back restores the page exactly.

Attorney profile pages still show an explanation rather than a selector, since
applying the editorial layout over the profile system breaks it — that was the
4.5.1 fix and it stands.

---

## 4.7.0 — Editorial blocks on any page

The dark umber panels, cards, fee boxes, quotes, and buttons from the editorial
pages are now **block styles**. Select a block, open Styles in the sidebar, pick
the editorial variant. No wrapper, no pasted markup, no page template — and it
works on any page whether or not it uses the editorial layout.

| Block | Styles offered |
| --- | --- |
| Group, Columns, Media & Text | Editorial panel |
| Group, Column | Editorial card |
| Quote | Editorial quote |
| Button | Editorial button |

The card carries a brick spine down its right edge and lifts on hover; add the
class `is-alt` for the olive spine to alternate across a row. Add `brooks-meta`
to a paragraph inside a panel or card for the small-caps label.

For a fee box, use a Group with **Editorial card**, then style the figure bold —
or apply `is-style-brooks-fee` directly if you are editing HTML.

The palette is declared on the style classes rather than inherited from `.blfE`,
which is what lets these work on an ordinary page. Hover lift is disabled under
`prefers-reduced-motion`, and the same stylesheet loads in the editor so the
boxes look right while you write.

---

## 4.8.0 — Case file bar, editorial headings, atmosphere

Three more pieces of the editorial pages, available anywhere.

**Case file bar** — the monospace docket strip. Block style on a Paragraph;
anything in bold picks up the brick accent.

**Editorial heading** — display serif with the brick rule beneath. Block style
on a Heading; add the class `is-ochre` for the ochre rule on a second level.

**Atmosphere** — the mocha ground with the cloud scene drifting behind, fading
from storm to clear as the visitor scrolls, exactly as the editorial pages do.
Switch it on per page in the Atmosphere box; allow or disallow it site-wide in
Customizer → Editorial Layout.

The atmosphere is deliberately not offered on a page that already carries the
editorial layout or the profile layout — those have their own scene, and two
would stack. Only the weather pair is used, not the river layers, which belong
to the editorial layout's own sticky sky.

`assets/js/atmosphere.js` is ~1KB, deferred, and loads only where the option is
on. Under reduced motion the scene renders in its clear state without animating.

---

## 4.9.0 — Matched to the live design

Corrections after reviewing the live pages side by side.

- **Atmosphere ground is now cream**, with the cloud scene as a faint pencil
  layer (14–18% through the fade) — matching the live pages, where the dark
  brown lives in the panels, not the ground. A `blf-atmos-dark` body class
  keeps the mocha version available if a page ever wants it.
- **Editorial heading** is now the live treatment: cream plate, thin ochre
  underline — and any *italicised* words inside it turn brick, which is how
  the two-tone hero lines work ("One sale can start / *two separate cases.*").
  Add `is-bare` for the plateless version used over the atmosphere.
- **New: Editorial statement** (Group or Cover) — the near-black block with a
  mono kicker top-left and a large serif line at the bottom. Add `is-brick`
  for the rust variant ("The fine ends. The question on the form does not.").

Case file bar, cards, fee boxes, and the button were already faithful and are
unchanged.

---

## 4.10.0 — The last three pieces, as patterns

Ghost cards, the drag-scroll carousel, and the photo caption need markup
structure, so they ship as **block patterns**: Inserter → Patterns →
Brooks Law Firm. Each drops in as ordinary core blocks, fully editable.

- **Editorial ghost card** — the giant letters are a real paragraph
  (`ghost-letters` class) the editor types, so SUB becomes DUI in two
  keystrokes. `is-alt` flips the spine to olive.
- **Editorial card carousel** — a Group that scroll-snaps sideways with the
  live pages' "drag / scroll →" hint. Touch and trackpad scroll natively;
  `assets/js/carousel-drag.js` (~1KB, deferred) adds mouse drag, and loads
  only on pages that contain a carousel. A drag never fires the card link.
- **Editorial photo with caption** — a Cover with the warm scrim, mono kicker
  top-left, serif caption bottom-left. Swap the image as on any Cover.

The patterns file into the existing "Brooks Law Firm" category from
inc/block-editor.php — one more collision caught before shipping, not after:
the theme already had a `brooks_law_register_patterns()`, so this module uses
`brooks_law_editorial_patterns()`.

---

## 4.11.0 — Homepage Style switcher

**Customizer → Homepage Style** switches the whole front page between looks:

- **Classic** — today's navy and limestone. Remains the default: activating
  4.11 changes nothing until you choose.
- **Editorial** — paper cream ground with the cloud scene faint behind it,
  clearing storm-to-sun as the visitor scrolls; hero as an umber statement
  with the two-tone italic treatment; Action Center and practice cards as
  umber floating boxes with brick/olive spines; contact band in the statement
  style.

Purely a body class plus one stylesheet (`assets/css/home-editorial.css`),
loaded only when Editorial is chosen. No template edits, no content changes,
no settings migrated — switching is instant and lossless in both directions.
The choices array is filterable (`brooks_law_home_style_choices`) so a third
style can be added later. The page-level Atmosphere checkbox stands down on
the front page while Editorial is active, so scenes never stack. Every colour
pair verified ≥4.5:1; scene motion honours reduced-motion.

---

## 4.12.0 — Content Extras

Four patterns the federal design system uses that this theme did not have.
Nothing changes on upload: the two template features are off until switched on.

**Numbered steps** — block style on a List or Group. Numerals in a filled disc
with a connector rail between them, for the sequential explanations that run
through most of these pages. Turns brick on the editorial ground automatically.

**Callout** — block style on a Paragraph or Group, for a deadline or statutory
warning that should not read as an ordinary paragraph. Add `is-urgent` for the
brick variant. A paragraph given class `brooks-meta` inside becomes the label.

**Previous / next links on posts** — Customizer → Content Extras. Adds two
contextual internal links to the bottom of every post, which is free internal
linking for the theft cluster. Renders nothing when there is no neighbour, and
never on pages.

**Return-to-top** — Customizer → Content Extras. A real anchor to `#top`, so it
works with JavaScript off; the ~700-byte deferred script only handles
show-on-scroll and moves focus to the top of the document on activation, so
keyboard users land where the page went. Sits above the sticky contact bar on
phones.

Colours come from the theme's existing custom properties with literal
fallbacks, so these follow Design Studio settings rather than adding a third
palette.

Verified against the live 4.11.0 theme and the Essentials plugin: no duplicate
function names across 162 theme and 66 plugin functions, no CSS class defined
twice, no enqueue-priority clash.

---

## 5.0.0 — Tokens, container queries, prefetch, lean delivery

A foundation pass. No new visual features: this makes what exists cheaper to
serve and cheaper to change.

**Design tokens** — `assets/css/tokens.css` defines the editorial palette once.
125 hardcoded colours across the three component stylesheets became variable
references. Values are declared in sRGB hex first, then in OKLCH inside
`@supports`, so browsers with OKLCH get perceptually uniform gradients and
`color-mix()` hover states while older ones keep working. The theme's own
tokens in style.css are untouched and still authoritative for the classic
design.

`editorial-pages.css` and `profile-pages.css` were deliberately **not**
tokenized — they are verbatim extractions that must keep matching the live
pages byte for byte.

**Conditional loading** — the component stylesheets used to load everywhere.
They now load only when a request can actually display a component:

| | 4.12 | 5.0 |
| --- | --- | --- |
| Ordinary interior page | 67.1 KB CSS | **43.3 KB** |
| Page using a component | 67.1 KB | 71.6 KB |

23.7 KB saved on most of the site. The test errs toward loading: an
unnecessary download is minor, an unstyled component is a visible bug.

**Container queries** — cards size their contents to the space they are given
rather than the viewport, so the same card works in a full-width panel, a
carousel, and a sidebar. Wrapped in `@supports`.

**Speculation Rules** — prefetch on hover, so the next page is usually already
loaded when clicked. Prefetch only, never prerender: prerendering executes
scripts and fires analytics on pages nobody visited, which would corrupt Search
Console. Stands down automatically if WordPress is handling this itself.

**`content-visibility`** — add class `blf-defer` to a long section to skip its
rendering work until it scrolls near. `contain-intrinsic-size` is supplied so
the scrollbar does not jump.

**Adaptive** — `prefers-contrast: more` raises borders and lifts muted text;
`prefers-color-scheme: dark` grounds editorial pages on umber instead of paper.
`text-wrap: balance` on headings, `pretty` on body text.

### Deliberately not done

**Cascade layers.** `@layer` only pays off with full adoption — unlayered CSS
beats layered CSS regardless of specificity, so wrapping only the new files
would make them lose to style.css in ways that would surface as random visual
bugs. Doing it properly means restructuring all 44KB of style.css, which is a
separate, riskier job than this pass.

---

## 5.0.1 — Fix: scene rendering full-size above the header

5.0 declared the new stylesheets as depending on a style handle named
`brooks-law`. The theme's actual handle is `brooks-law-style`. WordPress
silently refuses to enqueue a stylesheet whose dependency is not registered,
so `tokens.css`, `editorial-blocks.css`, and `ui-components.css` never loaded.

The atmosphere scene markup is printed by PHP and is not gated by the same
check, so on pages with Atmosphere or the editorial homepage switched on, the
SVGs rendered at natural size in normal document flow — pushing the header,
navigation, and everything else down the page.

One-line fix in two files. No other change.

---

## 5.1.0 — style.css tokenized; cascade layers tested and rejected

**Tokenized.** 67 colour literals in `style.css` became variable references,
and nine recurring values were promoted to named tokens in `:root`:
`--rule`, `--rule-cool`, `--rule-cool-2`, `--paper-warm`, `--rule-warm`,
`--mute-warm`, `--rule-warm-2`, `--rule-warm-3`, `--mute-cool`.

The stylesheet now holds 30 custom properties and 283 `var()` references,
with 9 genuine one-offs left as literals — each used exactly once, where a
token would add indirection without adding control.

**Verified by computed-style diff, not by eye.** The old and new stylesheets
were rendered against the same markup — header, topbar, dropdown, hero, action
centre tiles including the hot variant, practice cards, results, quote, button,
dark contact band, footer — and all 22 computed properties compared on every
element, at 1200px and 390px:

    viewport 1200px — elements:70  differing:0
    viewport 390px  — elements:70  differing:0

Zero visual change. All 18 Design Studio properties confirmed still defined.

### Cascade layers — tested, and deliberately not adopted

Wrapping the theme in `@layer` was tested against a simulation of WordPress's
own `wp-block-library`, which ships **unlayered**. Unlayered CSS beats layered
CSS regardless of source order or specificity, so core's block styles won:

    button background  expected #9c4a2f (theme)   got #32373c (core)
    button radius      expected 5px               got 9999px

Every core-styled block would have reverted. The only fixes are dequeuing core
block styles, or re-importing them inside a layer via `@import`, which is
render-blocking — the exact cost removed from the fonts in 4.3.

Recorded here so this is not re-litigated: cascade layers are a poor fit for a
classic theme that relies on core block styles, and the specificity discipline
this theme already uses (unique prefixes, collision checks before writing) is
the correct substitute.

---

## 5.2.0 — Site Tuning

The remainder of what security/performance plugins sell, after auditing what
this stack already covers. Duplication check came first: the theme already
strips emoji scripts and the generator tag (functions.php), and Brooks
Essentials already disables XML-RPC, removes the pingback header, and closes
comments. This module adds only the three genuinely missing pieces, each a
Customizer toggle under **Site Tuning**, on by default, invisible to visitors:

- **Security headers** — nosniff, SAMEORIGIN framing, strict-origin referrer
  policy, and a Permissions-Policy declining camera/mic/location/payment.
  **HSTS deliberately omitted**: Cloudflare owns transport for this domain,
  and two owners for one header is the orphaned-cache-rule mistake again.
- **REST user-enumeration lockdown** — /wp-json/wp/v2/users stops listing
  account usernames to logged-out requests. Admin and editing unaffected.
- **Head cleanup remainder** — RSD, shortlink, oEmbed discovery, and extra
  feed links (including comment feeds, closed site-wide anyway). The main
  content feed stays.

All three verified: toggles gate correctly, admin requests untouched,
logged-in REST untouched, existing headers preserved, main feed kept.

---

## 5.2.2 — Cross-browser CSS fixes

Two defects found in a mobile/browser-compatibility audit of 5.2.1.

* **`assets/css/editorial-pages.css`** — a stray `}` after `.blfE .wx .sunspin`
  caused the CSS parser to consume the `@keyframes blfDrift` rule that followed
  it as part of an error-recovery block, so the drifting-cloud animation on
  editorial pages silently did nothing in every browser. The extra brace is
  removed.
* **`assets/css/tokens.css`** — `--u-brick-hover` and `--u-ochre-soft` used
  relative colour syntax, `oklch(from ...)`, inside an `@supports` test that
  only checked for plain `oklch()`. Relative colour syntax shipped later
  (Safari 16.4 / Chrome 119) than `oklch()` did (Safari 15.4 / Chrome 111), so
  on a browser in that gap both tokens computed invalid and rendered
  transparent. They now sit in their own `@supports (color: oklch(from #000 l
  c h))` block, and the `color-mix()` definitions hold everywhere else.

No markup, template, PHP, or Customizer changes — visual output on current
browsers is identical to 5.2.1 apart from the cloud drift now running.

## 5.2.1 — Removed the half-dark mode

5.0's tokens.css switched Atmosphere pages to the umber ground when the
visitor's OS prefers dark. In practice the content blocks keep their light
backgrounds, so dark-mode visitors got light cards floating on near-black — a
patchwork, not a dark mode. The rule is removed; pages render identically in
light and dark OS settings. `prefers-contrast: more` support stays, since it
adjusts edges rather than repainting grounds. A real dark mode, if ever
wanted, is a designed project across every component — not one media query.
