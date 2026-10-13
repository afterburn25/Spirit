#!/usr/bin/env python3
"""Run the post-acceptance database-failure regression in an isolated install."""
from __future__ import annotations
import os
import subprocess
import tempfile
from pathlib import Path
from http_integration import ROOT, build_package, site


def main():
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Non-root disposable database required.')
    with tempfile.TemporaryDirectory(prefix='spirit-mail-receipt-') as directory:
        base = Path(directory); archive, code = build_package(base / 'packages')
        with site(base / 'website', archive, code, 'band') as current:
            current.claim(); current.client.form('install/', current.input())
            env = {**current.env, 'SPIRIT_FIXTURE_ROOT': str(current.root), 'SPIRIT_FIXTURE_DOCUMENT_ROOT': str(current.docroot)}
            subprocess.run(['php', str(ROOT / 'tests/mail_acceptance.php')], env=env, check=True, timeout=20)
    print('Post-acceptance database failure and duplicate-risk checks passed.', flush=True)


if __name__ == '__main__':
    main()
