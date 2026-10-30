#!/usr/bin/env python3
"""Read-only staging Admin browser acceptance. Credentials never enter logs or artifacts."""
import json
import os
import re
import sys
from pathlib import Path
from urllib.parse import urljoin, urlsplit
from playwright.sync_api import sync_playwright

def staging_config():
    base = os.getenv('SPIRIT_STAGING_BASE_URL', '').strip().rstrip('/') + '/'
    email = os.getenv('SPIRIT_STAGING_ADMIN_EMAIL', '').strip()
    password = os.getenv('SPIRIT_STAGING_ADMIN_PASSWORD', '')
    if not base.strip('/') or not email or not password:
        raise ValueError('Staging URL, email and password secrets are all required.')
    url = urlsplit(base)
    host = (url.hostname or '').lower().rstrip('.')
    labels = host.split('.')
    approved = {'stage','staging','preview','test','sandbox','demo','dev'}
    if (url.scheme != 'https' or url.username or url.password or url.query or url.fragment
            or url.port not in (None, 443) or len(labels) < 3 or
            not any(label in approved for label in labels[:-2])
            or host in {'spiritualgorillaz.com','www.spiritualgorillaz.com'}
            or not re.fullmatch(r'[a-z0-9.-]+', host)):
        raise ValueError('An independent HTTPS staging subdomain is required, not production.')
    return base, host, email, password

def main():
    base, host, email, password = staging_config()
    destination = Path(os.getenv('SPIRIT_STAGING_REPORT_DIR', '/tmp/spirit-staging'))
    destination.mkdir(parents=True, exist_ok=True)
    result = {'mode':'staging-read-only','checked':[],'failures':[]}
    with sync_playwright() as pw:
        browser = pw.chromium.launch()
        context = browser.new_context(viewport={'width':1440,'height':900},
                                      accept_downloads=False,service_workers='block')
        def route_request(route):
            u = urlsplit(route.request.url)
            permitted_write = route.request.method == 'POST' and u.path.endswith('/admin/login.php')
            if u.scheme in ('http','https') and (u.scheme != 'https' or u.hostname != host or
                (route.request.method not in ('GET','HEAD') and not permitted_write)):
                route.abort()
            else:
                route.continue_()
        context.route('**/*', route_request)
        page = context.new_page()
        page.on('pageerror', lambda _:result['failures'].append('Browser script exception'))
        try:
            response = page.goto(urljoin(base,'admin/login.php'),wait_until='domcontentloaded',timeout=25000)
            if not response or response.status!=200 or page.locator('input[name=email]').count()!=1:
                raise RuntimeError('Staging login page unavailable')
            page.locator('input[name=email]').fill(email)
            page.locator('input[name=password]').fill(password)
            page.get_by_role('button',name='Enter backstage').click(timeout=12000)
            page.wait_for_load_state('domcontentloaded',timeout=18000)
            if (urlsplit(page.url).hostname!=host or page.url.endswith('login.php')
                    or not page.locator('#sidebar').count()):
                raise RuntimeError('Staging login failed; no retry attempted')
            result['checked'].append('Staging login and sidebar')
            hrefs=page.locator('#sidebar nav a[href]').evaluate_all(
                '(anchors)=>anchors.map(a=>a.getAttribute("href"))')
            targets=[]
            root=urlsplit(base).path
            for href in hrefs:
                url=urljoin(base,href or '')
                u=urlsplit(url)
                if (u.scheme!='https' or u.hostname!=host or
                    not u.path.startswith(root+'admin/') or
                    any(part in u.path.lower() for part in ('logout','delete','remove','export','download','reset','recover'))):
                    continue
                if url not in targets:
                    targets.append(url)
            if not targets:
                raise RuntimeError('No permitted admin links discovered')
            for url in targets[:45]:
                path=urlsplit(url).path
                try:
                    response=page.goto(url,wait_until='domcontentloaded',timeout=22000)
                    if not response or response.status!=200 or urlsplit(page.url).hostname!=host:
                        result['failures'].append('Admin HTTP/navigation: '+path)
                    elif not page.locator('main h1').count():
                        result['failures'].append('Missing page heading: '+path)
                    elif not path.rstrip('/').endswith('/admin/help') and not page.locator('.admin-context-help').count():
                        result['failures'].append('Missing contextual Help: '+path)
                    else:
                        result['checked'].append('Admin screen: '+path)
                except Exception:
                    result['failures'].append('Admin timeout: '+path)
            if any('kind=page' in url for url in targets):
                for kind in ('page','member'):
                    response=page.goto(urljoin(base,'admin/content/edit.php?kind='+kind),
                                       wait_until='domcontentloaded',timeout=22000)
                    if not response or response.status!=200 or not page.locator('form.editor-layout').count():
                        result['failures'].append('Editor unavailable: '+kind)
                    elif not page.locator('input[name=direct_image_id]').count():
                        result['failures'].append('Direct image upload missing: '+kind)
                    else:
                        result['checked'].append('Read-only editor inspection: '+kind)
            for width in (1440,390):
                page.set_viewport_size({'width':width,'height':900})
                response=page.goto(urljoin(base,'admin/help/?topic=pages'),
                                   wait_until='domcontentloaded',timeout=22000)
                if not response or response.status!=200 or not page.locator('.help-guide').count():
                    result['failures'].append('Help unavailable: '+str(width))
                    continue
                if page.evaluate('document.documentElement.scrollWidth>innerWidth+1'):
                    result['failures'].append('Help overflow: '+str(width))
                page.screenshot(path=str(destination/('help-'+str(width)+'-redacted.png')),
                                full_page=True,mask=[page.locator('.user-chip'),
                                                     page.locator('input'),page.locator('textarea')])
                result['checked'].append('Redacted Help screenshot at '+str(width))
        except Exception as e:
            result['failures'].append(type(e).__name__+': staging sign-in or review incomplete')
        finally:
            context.close()
            browser.close()
    (destination/'report.json').write_text(json.dumps(result,indent=2)+'\n',encoding='utf8')
    print('Staging screens checked:',len(result['checked']),'; failures:',len(result['failures']))
    for failure in result['failures']:
        print('FAIL '+failure,file=sys.stderr)
    return 1 if result['failures'] else 0

if __name__=='__main__':
    sys.exit(main())
