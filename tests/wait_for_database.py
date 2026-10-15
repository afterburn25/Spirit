#!/usr/bin/env python3
"""Wait for the disposable account, never create resources or retry a test."""
from __future__ import annotations
import json
import os
import re
import subprocess
import time
from pathlib import Path


def main() -> None:
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Use an unprivileged disposable test environment.')
    if os.environ.get('SPIRIT_DB_HOST') not in ('127.0.0.1', 'localhost') or not re.fullmatch(
        r'spirit_ci(?:_[a-z0-9]+)?', os.environ.get('SPIRIT_DB_NAME', '')
    ):
        raise RuntimeError('Refusing a nonlocal or non-test database readiness probe.')
    env = {**os.environ, 'SPIRIT_DB_PREFIX': 'sgci_00000000_'}
    command = ['php', str(Path(__file__).with_name('db_fixture.php')), 'ping']
    deadline = time.monotonic() + 60
    attempts = 0
    while time.monotonic() < deadline:
        attempts += 1
        try:
            result = subprocess.run(command, env=env, capture_output=True, text=True,
                                    timeout=min(5, max(.1, deadline - time.monotonic())))
            if result.returncode == 0 and json.loads(result.stdout).get('ready') is True:
                print(f'Disposable SQL account ready after {attempts} probe(s). No tables changed.', flush=True)
                return
        except (subprocess.TimeoutExpired, json.JSONDecodeError):
            pass
        time.sleep(min(.5, max(0, deadline - time.monotonic())))
    # Do not disclose environment variables or raw driver output.
    raise RuntimeError('Disposable SQL account was not ready within 60 seconds; no test ran.')


if __name__ == '__main__':
    main()
