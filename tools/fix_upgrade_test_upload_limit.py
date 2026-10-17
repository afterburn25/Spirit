#!/usr/bin/env python3
"""Make the upgrade integration fixture accept the production-size package."""
from pathlib import Path


def replace_once(text: str, old: str, new: str, label: str) -> str:
    if old not in text:
        raise RuntimeError(f"Expected {label} target was not found")
    return text.replace(old, new, 1)


http_path = Path("tests/http_integration.py")
http = http_path.read_text(encoding="utf-8")
http = replace_once(
    http,
    "class Site:\n    def __init__(self, area: Path, archive: Path, code: None, subfolder: str):",
    "class Site:\n    def __init__(self, area: Path, archive: Path, code: None, subfolder: str, php_ini: dict[str, str] | None = None):",
    "Site constructor",
)
http = replace_once(
    http,
    "        self.log = (area / 'php.log').open('w+')\n        self.process = subprocess.Popen(['php','-d','display_errors=0','-S',f'127.0.0.1:{port}','-t',str(self.docroot)], cwd=self.root, env=self.env, stdout=self.log, stderr=self.log)",
    "        self.log = (area / 'php.log').open('w+')\n        php_command = ['php', '-d', 'display_errors=0']\n        for key, value in (php_ini or {}).items():\n            php_command.extend(['-d', f'{key}={value}'])\n        php_command.extend(['-S', f'127.0.0.1:{port}', '-t', str(self.docroot)])\n        self.process = subprocess.Popen(php_command, cwd=self.root, env=self.env, stdout=self.log, stderr=self.log)",
    "PHP server command",
)
http = replace_once(
    http,
    "@contextlib.contextmanager\ndef site(area: Path, archive: Path, code: None, subfolder: str = ''):\n    instance = Site(area, archive, code, subfolder)",
    "@contextlib.contextmanager\ndef site(area: Path, archive: Path, code: None, subfolder: str = '', php_ini: dict[str, str] | None = None):\n    instance = Site(area, archive, code, subfolder, php_ini)",
    "site context manager",
)
http_path.write_text(http, encoding="utf-8")

upgrade_path = Path("tests/upgrade_integration.py")
upgrade = upgrade_path.read_text(encoding="utf-8")
upgrade = replace_once(
    upgrade,
    "TEST_UPDATE_VERSION=f'{_core[1]}.{_core[2]}.{int(_core[3])+1}-dev.999'\n",
    "TEST_UPDATE_VERSION=f'{_core[1]}.{_core[2]}.{int(_core[3])+1}-dev.999'\nUPGRADE_TEST_PHP_INI={'upload_max_filesize':'64M','post_max_size':'72M'}\n",
    "upgrade PHP INI fixture constant",
)
upgrade = replace_once(
    upgrade,
    "            with site(base/('root' if not subfolder else 'nested'),archive,code,subfolder) as s:",
    "            with site(base/('root' if not subfolder else 'nested'),archive,code,subfolder,php_ini=UPGRADE_TEST_PHP_INI) as s:",
    "primary upgrade site fixture",
)
upgrade = replace_once(
    upgrade,
    "        with site(base/'faults',archive,code,'band') as s:",
    "        with site(base/'faults',archive,code,'band',php_ini=UPGRADE_TEST_PHP_INI) as s:",
    "fault recovery site fixture",
)
upgrade = replace_once(
    upgrade,
    "        with site(base/'rate-limit',archive,code,'band') as s:",
    "        with site(base/'rate-limit',archive,code,'band',php_ini=UPGRADE_TEST_PHP_INI) as s:",
    "rate limit site fixture",
)
upgrade_path.write_text(upgrade, encoding="utf-8")
