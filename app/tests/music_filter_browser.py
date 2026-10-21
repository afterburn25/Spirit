#!/usr/bin/env python3
"""Offline Music-filter component regression using production PHP and CSS.

An explicitly empty data layer replaces only database-backed fixture providers.
The filter navigation is extracted from the real PHP output with its original
layout ancestors. Unrelated decoration uses a transparent image, and scripts are
not loaded. This is a CSS/component regression, not a full-page, HTTP, database
installation, or upgrade test.

python3 app/tests/music_filter_browser.py --output /tmp/spirit-filter-review
Use --baseline-ref <git-ref> to reproduce the original 320px overflow.
Requires PHP, BeautifulSoup and Playwright with a Chromium executable.
"""
from __future__ import annotations
import argparse
import hashlib
import json
import os
from pathlib import Path
import re
import shutil
import subprocess
import tempfile
from bs4 import BeautifulSoup
from playwright.sync_api import sync_playwright

SOURCE = Path(__file__).resolve().parents[2]
FIXTURE = SOURCE / 'app/tests/fixtures/music_filter_render.php'
WIDTHS = (320,360,390,768,1024,1440)
LABELS = ('All releases','Albums','Singles','EPs')
TYPES = ('all','album','single','ep')


def main() -> int:
    parser=argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--output',type=Path,required=True)
    parser.add_argument('--code',type=Path,default=SOURCE)
    parser.add_argument('--baseline-ref')
    parser.add_argument('--browser',default=shutil.which('chromium'))
    args=parser.parse_args()
    code=args.code.resolve();out=args.output.resolve();out.mkdir(parents=True,exist_ok=True)
    checks=[]; errors=[];baseline=[]
    def check(condition,message):
        checks.append({'pass':bool(condition),'message':message})
        if not condition:raise AssertionError(message)
    css=(code/'assets/css/site.css').read_text()
    # Decorative backgrounds do not determine filter geometry. Avoid any network
    # dependency while retaining the committed stylesheet and original ancestors.
    transparent='data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=='
    css=re.sub(r'url\(([^)]+)\)', 'url("'+transparent+'")',css)
    def render(base='',kind='all',sections=None):
        env=dict(os.environ,SPIRIT_CODE=str(code))
        if sections:env['SPIRIT_SECTIONS_FIXTURE']=str(sections)
        process=subprocess.run(['php',str(FIXTURE),base,kind],env=env,capture_output=True,check=True,timeout=15)
        if process.stderr:raise RuntimeError(process.stderr.decode(errors='replace'))
        soup=BeautifulSoup(process.stdout.decode(),'html.parser')
        navigation=soup.find('nav',attrs={'aria-label':'Release type'})
        if navigation is None:raise RuntimeError('Production filter navigation missing')
        return ('<!doctype html><html lang="en"><head><meta charset="utf-8">'
                '<meta name="viewport" content="width=device-width,initial-scale=1">'
                '<title>Isolated Music filter regression</title><style>'+css+'</style></head>'
                '<body class="public-site"><div class="site-shell"><main id="site-content" data-page="music">'
                '<section class="section music-section">'+str(navigation)+'</section></main></div></body></html>')
    with tempfile.TemporaryDirectory(prefix='spirit-filter-fixture-') as temp:
        baseline_path=Path(temp)/'baseline-sections.php'
        if args.baseline_ref:
            baseline_path.write_bytes(subprocess.check_output(['git','show',args.baseline_ref+':app/views/public/sections.php'],cwd=SOURCE))
        with sync_playwright() as p:
            opts={'headless':True,'args':['--no-sandbox']}
            if args.browser:opts['executable_path']=args.browser
            browser=p.chromium.launch(**opts);version=browser.version
            try:
                for base in ('','/band'):
                    html=render(base)
                    context=browser.new_context(viewport={'width':320,'height':900},reduced_motion='reduce',device_scale_factor=1)
                    page=context.new_page();page.on('pageerror',lambda e:errors.append(str(e)))
                    page.set_content(html,wait_until='domcontentloaded')
                    for width in WIDTHS:
                        print('Checking',base or 'root',width,flush=True)
                        page.set_viewport_size({'width':width,'height':900});page.wait_for_timeout(100)
                        nav=page.get_by_role('navigation',name='Release type')
                        check(tuple(nav.locator('a').all_text_contents())==LABELS,f'{base or "root"} {width}px: all four filters retained')
                        metrics=nav.evaluate('''n=>{const r=n.getBoundingClientRect(),s=getComputedStyle(n),left=r.left+parseFloat(s.paddingLeft)+parseFloat(s.borderLeftWidth),right=r.right-parseFloat(s.paddingRight)-parseFloat(s.borderRightWidth);return {
                            wrap:getComputedStyle(n).flexWrap,overflow:document.documentElement.scrollWidth>innerWidth+1,
                            links:[...n.querySelectorAll('a')].map(a=>{const b=a.getBoundingClientRect();return {
                                label:a.textContent.trim(),left:b.left,right:b.right,top:b.top,
                                fits:b.left>=left-1&&b.right<=right+1&&b.right<=innerWidth+1&&b.left>=-1};})};}''')
                        check(metrics['wrap']=='wrap',f'{base or "root"} {width}px: filter row wraps')
                        check(not metrics['overflow'],f'{base or "root"} {width}px: no horizontal page overflow')
                        for item in metrics['links']:check(item['fits'],f'{base or "root"} {width}px: {item["label"]} stays inside its container')
                        for label,kind in zip(LABELS,TYPES):
                            check(nav.get_by_role('link',name=label,exact=True).get_attribute('href')==base+'/music/?type='+kind,
                                  f'{base or "root"} {width}px: {label} keeps its original URL')
                        nav.locator('a').first.focus()
                        for index,label in enumerate(LABELS):
                            if index:page.keyboard.press('Tab')
                            check(nav.get_by_role('link',name=label,exact=True).evaluate('(n)=>n===document.activeElement'),
                                  f'{base or "root"} {width}px: keyboard reaches {label}')
                        nav.scroll_into_view_if_needed()
                        page.screenshot(path=str(out/f'{"band" if base else "root"}-music-{width}.png'))
                        nav.screenshot(path=str(out/f'{"band" if base else "root"}-filters-{width}.png'))
                    context.close()
                    # Server-rendered filter selection and native wrapping need no JS.
                    context=browser.new_context(viewport={'width':320,'height':900},java_script_enabled=False)
                    page=context.new_page()
                    for label,kind in zip(LABELS,TYPES):
                        page.set_content(render(base,kind),wait_until='domcontentloaded')
                        nav=page.get_by_role('navigation',name='Release type')
                        check(nav.locator('[aria-current="page"]').text_content().strip()==label,f'{base or "root"} no-JS: PHP selects {label}')
                        check(nav.evaluate('(n)=>getComputedStyle(n).flexWrap')=='wrap',f'{base or "root"} no-JS: {label} view wraps')
                    context.close()
                if args.baseline_ref:
                    context=browser.new_context(viewport={'width':320,'height':900},reduced_motion='reduce')
                    page=context.new_page();page.set_content(render(sections=baseline_path),wait_until='domcontentloaded')
                    nav=page.get_by_role('navigation',name='Release type')
                    baseline=nav.evaluate('''n=>{const r=n.getBoundingClientRect(),s=getComputedStyle(n),left=r.left+parseFloat(s.paddingLeft)+parseFloat(s.borderLeftWidth),right=r.right-parseFloat(s.paddingRight)-parseFloat(s.borderRightWidth);return [...n.querySelectorAll('a')].map(a=>{
                        const b=a.getBoundingClientRect();return {label:a.textContent.trim(),right:b.right,container_right:right,
                        fits:b.left>=left-1&&b.right<=right+1};});}''')
                    print('Baseline geometry:',json.dumps(baseline),flush=True)
                    check(any(not item['fits'] for item in baseline),'Published template reproduces original 320px filter overflow')
                    nav.scroll_into_view_if_needed();page.screenshot(path=str(out/'published-rc10-baseline-320.png'))
                    nav.screenshot(path=str(out/'published-rc10-baseline-filters-320.png'))
                    context.close()
                check(not errors,'No browser errors in corrected offline component views')
                report={'scope':'Offline filter-component render extracted from production PHP with committed CSS and original layout ancestors; decorative images substituted, scripts omitted; not a full-page/HTTP/installed-site test',
                        'browser':'Chromium '+version,'widths':list(WIDTHS),'base_paths':['/','/band/'],
                        'version':(code/'VERSION').read_text().strip(),'passed':len(checks),'failed':0,'checks':checks,
                        'baseline_ref':args.baseline_ref,'baseline_320':baseline,'browser_errors':errors,
                        'template_sha256':hashlib.sha256((code/'app/views/public/sections.php').read_bytes()).hexdigest(),
                        'css_sha256':hashlib.sha256((code/'assets/css/site.css').read_bytes()).hexdigest()}
                (out/'music-filter-verification.json').write_text(json.dumps(report,indent=2)+'\n')
                print(f'PASS {len(checks)} offline real-component browser checks; baseline overflow reproduced.',flush=True)
            finally:browser.close()
    return 0
if __name__=='__main__':raise SystemExit(main())
