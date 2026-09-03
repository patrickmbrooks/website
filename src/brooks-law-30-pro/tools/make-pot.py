import re, os, datetime, sys
DOMAIN = sys.argv[1] if len(sys.argv) > 1 else 'brooks-law-30-pro'
NAME   = sys.argv[2] if len(sys.argv) > 2 else 'Brooks Law Pro 5.3.0'
entries = {}
pat = re.compile(
  r"(?P<fn>__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e|_n|_x|esc_html_x|_nx)"
  r"\(\s*(?P<args>(?:'(?:[^'\\]|\\.)*'|\"(?:[^\"\\]|\\.)*\"|\s|,)*?)\s*'" + DOMAIN + r"'\s*\)")
strlit = re.compile(r"'((?:[^'\\]|\\.)*)'|\"((?:[^\"\\]|\\.)*)\"")

files = []
for root, d, fs in os.walk('.'):
    if '/languages' in root or '/assets/fonts' in root or '/node_modules' in root: continue
    for f in fs:
        if f.endswith('.php'): files.append(os.path.join(root, f).replace('./', ''))

for path in sorted(files):
    src = open(path, encoding='utf-8').read()
    for m in pat.finditer(src):
        line = src[:m.start()].count('\n') + 1
        args = [(a or b) for a, b in strlit.findall(m.group('args'))]
        if not args: continue
        fn = m.group('fn'); ctx = plural = None
        if fn in ('_n', '_nx'):
            msgid = args[0]; plural = args[1] if len(args) > 1 else None
            if fn == '_nx' and len(args) > 2: ctx = args[-1]
        elif fn in ('_x', 'esc_html_x'):
            msgid = args[0]; ctx = args[1] if len(args) > 1 else None
        else:
            msgid = args[0]
        tc = None
        for pl in reversed(src[:m.start()].split('\n')[-3:]):
            c = re.search(r'/\*\s*(translators:.*?)\s*\*/', pl, re.I | re.S)
            if c: tc = c.group(1); break
        key = (ctx, msgid, plural)
        entries.setdefault(key, {'refs': [], 'tc': None})
        entries[key]['refs'].append('%s:%d' % (path, line))
        if tc and not entries[key]['tc']: entries[key]['tc'] = tc

esc = lambda s: s.replace('\\', '\\\\').replace('"', '\\"').replace("\\'", "'").replace('\n', '\\n')
out = ['''# Copyright (C) 2026 Brooks Law Firm
# This file is distributed under the GNU General Public License v2 or later.
msgid ""
msgstr ""
"Project-Id-Version: %s\\n"
"Report-Msgid-Bugs-To: https://patrickbrookslaw.com/\\n"
"POT-Creation-Date: %s\\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 8bit\\n"
"Language-Team: LANGUAGE <LL@li.org>\\n"
"Plural-Forms: nplurals=2; plural=(n != 1);\\n"
"X-Domain: %s\\n"
''' % (NAME, datetime.datetime.utcnow().strftime('%Y-%m-%dT%H:%M:%S+00:00'), DOMAIN)]

commented = 0
for (ctx, msgid, plural), meta in sorted(entries.items(), key=lambda kv: kv[1]['refs'][0]):
    if meta['tc']:
        out.append('#. %s' % ' '.join(meta['tc'].split())); commented += 1
    out.append('#: %s' % ' '.join(meta['refs'][:6]))
    if ctx: out.append('msgctxt "%s"' % esc(ctx))
    out.append('msgid "%s"' % esc(msgid))
    if plural:
        out.append('msgid_plural "%s"' % esc(plural)); out.append('msgstr[0] ""'); out.append('msgstr[1] ""')
    else:
        out.append('msgstr ""')
    out.append('')

os.makedirs('languages', exist_ok=True)
open('languages/%s.pot' % DOMAIN, 'w', encoding='utf-8').write('\n'.join(out))
print('languages/%s.pot: %d strings (%d with translator notes) from %d files' % (DOMAIN, len(entries), commented, len(files)))
