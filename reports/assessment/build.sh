#!/usr/bin/env bash
# Rebuild the website assessment PDF.
#
# Two things this script exists to handle, both learned the hard way:
#
#   1. Fraunces and IBM Plex Sans ship from Google as VARIABLE fonts.
#      Chromium renders them on screen but will not embed them through the
#      --print-to-pdf path, so the PDF silently falls back to Liberation Sans.
#      They are instanced to static cuts first.
#   2. Even static fonts referenced by file are raced by the print renderer,
#      so they are inlined as data URIs before rendering.
#
# Requires: python3 with fonttools + brotli, and a Chromium binary.
set -euo pipefail
cd "$(dirname "$0")"

CHROME="${CHROME:-$(command -v chromium || command -v google-chrome || echo /opt/pw-browsers/chromium-1194/chrome-linux/chrome)}"
THEME=../../src/brooks-law-30-pro/assets/fonts

python3 - "$THEME" <<'PY'
import sys, os, base64, shutil
from fontTools.ttLib import TTFont
from fontTools.varLib import instancer

fonts = sys.argv[1]
# Instance the two variable faces to static cuts.
for src, out, pin in [('fraunces-600-latin.woff2', 'fraunces-static.woff2', {'wght': 600, 'opsz': 40}),
                      ('ibmplexsans-400-latin.woff2', 'plexsans-static.woff2', {'wght': 400})]:
    f = TTFont(os.path.join(fonts, src))
    if 'fvar' in f:
        loc = {a.axisTag: pin.get(a.axisTag, a.defaultValue) for a in f['fvar'].axes}
        f = instancer.instantiateVariableFont(f, loc, inplace=False, updateFontNames=False)
    f.flavor = 'woff2'
    f.save(out)

# The mono cuts are already static.
for m in ('ibmplexmono-400-latin.woff2', 'ibmplexmono-500-latin.woff2'):
    shutil.copy(os.path.join(fonts, m), m)

html = open('assessment.html', encoding='utf-8').read()
for name in ('fraunces-static.woff2', 'plexsans-static.woff2',
             'ibmplexmono-400-latin.woff2', 'ibmplexmono-500-latin.woff2'):
    blob = base64.b64encode(open(name, 'rb').read()).decode()
    html = html.replace('url(%s)' % name, 'url(data:font/woff2;base64,%s)' % blob)
open('.build.html', 'w', encoding='utf-8').write(html)
print('fonts instanced and inlined')
PY

"$CHROME" --headless --disable-gpu --no-sandbox --hide-scrollbars \
  --virtual-time-budget=10000 --no-pdf-header-footer \
  --print-to-pdf=../../dist/brooks-law-website-assessment.pdf .build.html
rm -f .build.html *.woff2
echo "wrote dist/brooks-law-website-assessment.pdf"
