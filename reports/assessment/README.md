# Website assessment

`assessment.html` is the source of the two-page PDF handed over on
3 September 2026. `build.sh` renders it to `dist/brooks-law-website-assessment.pdf`.

```
bash reports/assessment/build.sh
```

The PDF itself is not committed — `dist/` is ignored, and the document is
reproducible from source in one command.

## Why the build script is not just "print the HTML"

Two things bite, and both fail *silently* into a document that looks fine on
screen and wrong on paper:

1. **Fraunces and IBM Plex Sans ship as variable fonts.** Chromium renders them
   on screen but will not embed them through `--print-to-pdf`, so the PDF falls
   back to Liberation Sans with no error. The script instances them to static
   cuts first, which also drops them from 112 KB to 40 KB.
2. **Referenced fonts are raced by the print renderer** even when static, so
   they are inlined as data URIs before rendering.

Typefaces come from `src/brooks-law-30-pro/assets/fonts/` — the same faces the
theme self-hosts, so the document is set in the firm's own type.

## Figures in the document

Every score cites a measurement taken from the WordPress export of
3 September 2026 (170 published pages and posts, 459 attachments). If the
document is regenerated against a newer export, the figures in
`assessment.html` must be recomputed — they are written into the markup, not
derived at build time.
