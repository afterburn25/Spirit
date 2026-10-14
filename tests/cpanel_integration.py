#!/usr/bin/env python3
"""cPanel boundary simulator plus real dedicated local SQL users/databases."""
import os
import subprocess
import tempfile
from pathlib import Path
from http_integration import ROOT, build_package, site


def main():
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Use the non-root disposable database environment.')
    with tempfile.TemporaryDirectory(prefix='spirit-cpanel-') as directory:
        base=Path(directory);archive,unused=build_package(base/'packages')
        for mode in ['success','rejected','unknown','migration']:
            for folder in ['', 'band']:
                with site(base/(mode+('-nested' if folder else '-root')),archive,unused,folder) as current:
                    env={**current.env,'SPIRIT_FIXTURE_ROOT':str(current.root),'SPIRIT_FIXTURE_DOCUMENT_ROOT':str(current.docroot),'SPIRIT_FIXTURE_SCRIPT_NAME':('/'+folder if folder else '')+'/install/index.php'}
                    subprocess.run(['php',str(ROOT/'tests/cpanel_fixture.php'),mode],env=env,check=True,timeout=35)
    print('cPanel contract/retry and real SQL installation checks passed; real hosting acceptance remains.',flush=True)


if __name__=='__main__':main()
