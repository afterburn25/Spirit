#!/usr/bin/env python3
"""Preserve focused, synthetic maintenance failure evidence without changing tests."""
from __future__ import annotations
import html
import json
import os
import re
import traceback
from pathlib import Path
import upgrade_integration
import cleanup_integration


def main():
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Non-root disposable database required.')
    destination = Path(os.environ['RUNNER_TEMP']) / 'spirit-maintenance-evidence'
    destination.mkdir(parents=True, exist_ok=True)
    evidence = {'uploads': [], 'result': 'running'}
    original = upgrade_integration.upload

    def record_upload(*args, **kwargs):
        result = original(*args, **kwargs)
        # Record only displayed diagnostic notices, not inputs, keys or forms.
        notices = re.findall(r'<div[^>]*class="notice error"[^>]*>(.*?)</div>', result[1], re.S)
        evidence['uploads'].append({'http_status': result[0], 'notices': [html.unescape(re.sub('<[^>]+>', ' ', n)).strip() for n in notices]})
        return result

    upgrade_integration.upload = record_upload
    try:
        upgrade_integration.main()
        cleanup_integration.main()
        evidence['result'] = 'passed'
    except Exception as error:
        evidence['result'] = 'failed'
        evidence['exception_type'] = type(error).__name__
        evidence['traceback'] = traceback.format_exc()
        notices = evidence['uploads'][-1]['notices'] if evidence['uploads'] else []
        detail = ' | '.join(notices) or str(error)[:900]
        detail = detail.replace('%', '%25').replace('\r', '%0D').replace('\n', '%0A')
        print('::error title=Maintenance regression::' + detail, flush=True)
        raise
    finally:
        upgrade_integration.upload = original
        (destination / 'diagnostic.json').write_text(json.dumps(evidence, indent=2), encoding='utf-8')


if __name__ == '__main__':
    main()
