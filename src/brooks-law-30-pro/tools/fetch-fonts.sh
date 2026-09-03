#!/usr/bin/env bash
# Regenerate assets/fonts/ from Google Fonts.
#
# Downloads the Latin and Latin-Extended subsets of the editorial typefaces,
# de-duplicates the variable-font files that several weights share, and writes
# assets/fonts/fonts.css pointing at the local copies. Run from the theme root
# when a weight or family changes; the output is committed, so a deployment
# never reaches Google.
#
# Fraunces and IBM Plex are both SIL Open Font License 1.1, which permits this.
set -euo pipefail

cd "$(dirname "$0")/.."
UA='Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
URL='https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600&display=swap'

mkdir -p assets/fonts
curl -sS -m 60 -A "$UA" "$URL" -o /tmp/blf-fonts.css

python3 - "$URL" <<'PY'
import re, subprocess, os, hashlib, glob
UA = ('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 '
      '(KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36')
KEEP = {'latin', 'latin-ext'}

css = open('/tmp/blf-fonts.css').read()
blocks, out, files = re.split(r'(?=/\*\s*[a-z-]+\s*\*/)', css), [], {}

for b in blocks:
    m = re.match(r'/\*\s*([a-z-]+)\s*\*/', b.strip())
    if not m or m.group(1) not in KEEP:
        continue
    fam = re.search(r"font-family:\s*'([^']+)'", b)
    wt  = re.search(r'font-weight:\s*([\d\s]+);', b)
    url = re.search(r'url\((https://fonts\.gstatic\.com/[^)]+)\)', b)
    if not (fam and url):
        continue
    weight = (wt.group(1).strip() if wt else '400').replace(' ', '-')
    name = '%s-%s-%s.woff2' % (fam.group(1).replace(' ', '').lower(), weight, m.group(1))
    files[name] = url.group(1)
    out.append(re.sub(r'/\*\s*[a-z-]+\s*\*/\s*', '', b.replace(url.group(1), name)).strip())

for f in glob.glob('assets/fonts/*.woff2'):
    os.remove(f)
for name, url in files.items():
    subprocess.run(['curl', '-sS', '-m', '60', '-A', UA, '-o', 'assets/fonts/' + name, url], check=True)

# Several weights of a variable face are byte-identical; keep one copy.
seen, renamed = {}, {}
for f in sorted(glob.glob('assets/fonts/*.woff2')):
    h = hashlib.md5(open(f, 'rb').read()).hexdigest()
    base = os.path.basename(f)
    if h in seen:
        renamed[base] = seen[h]
        os.remove(f)
    else:
        seen[h] = base

body = '\n\n'.join(out)
for old, new in renamed.items():
    body = body.replace(old, new)

header = open('assets/fonts/fonts.css').read().split('*/')[0] + '*/\n' \
    if os.path.exists('assets/fonts/fonts.css') else ''
open('assets/fonts/fonts.css', 'w').write(header + '\n' + body + '\n')
print('%d @font-face rules, %d files' % (len(out), len(seen)))
PY

echo "assets/fonts regenerated:"
du -sh assets/fonts
