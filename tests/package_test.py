#!/usr/bin/env python3
"""Packaging regressions using an isolated fake tree, never real secrets."""
import hashlib
import importlib.util
import json
import re
import tempfile
import unittest
import zipfile
from pathlib import Path

SPEC=importlib.util.spec_from_file_location('spirit_packager',Path(__file__).resolve().parents[1]/'tools/package.py')
packager=importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(packager)

class PackagingTests(unittest.TestCase):
    def setUp(self):
        self.temp=tempfile.TemporaryDirectory(prefix='spirit-package-test-')
        self.root=Path(self.temp.name)/'source'; self.root.mkdir()
        self.old_root=packager.ROOT; packager.ROOT=self.root
        files={'VERSION':'0.1.0-dev.2','index.php':'<?php echo "test";','.htaccess':'Options -Indexes\n','app/Example.php':'<?php return [];','install/index.php':'<?php return [];','install/access.php':'old claim hash','config/.htaccess':'Require all denied\n','config/local.php':'FAKE-CREDENTIAL','storage/sessions/session':'FAKE-SESSION','uploads/customer.jpg':'FAKE-PRIVATE-MEDIA','tests/db_fixture.php':'TEST-ONLY','tools/dev.php':'DEV-ONLY','docs/INSTALLATION.md':'instructions','database/migrations/001.sql':'CREATE TABLE example(id INT);','database/customer-dump.sql':'FAKE-CUSTOMER-DUMP','docs/.env.local':'FAKE-ENV','assets/private.key':'FAKE-KEY','docs/old-PRIVATE-SETUP.txt':'OLD-CLAIM','custom-output/old-PRIVATE-SETUP.txt':'OLD-OUTPUT-CLAIM','images/logo.txt':'public image fixture'}
        for name,body in files.items():
            target=self.root/name; target.parent.mkdir(parents=True,exist_ok=True); target.write_text(body)
        (self.root/'assets/link').symlink_to(self.root/'config/local.php')
        self.output=self.root/'custom-output'
    def tearDown(self):
        packager.ROOT=self.old_root; self.temp.cleanup()
    def build(self):
        return packager.package(self.output)
    def test_keeps_deployment_files(self):
        archive=self.build()
        with zipfile.ZipFile(archive) as bundle:
            self.assertTrue({'index.php','.htaccess','app/Example.php','install/index.php','database/migrations/001.sql'}.issubset(bundle.namelist()))
    def test_drops_runtime_and_local_secrets(self):
        archive=self.build()
        with zipfile.ZipFile(archive) as bundle:
            for name in ['config/local.php','storage/sessions/session','uploads/customer.jpg','docs/.env.local','assets/private.key','database/customer-dump.sql']:
                self.assertNotIn(name,bundle.namelist())
    def test_drops_test_and_development_scripts(self):
        archive=self.build()
        with zipfile.ZipFile(archive) as bundle:
            self.assertFalse(any(name.startswith(('tests/','tools/')) for name in bundle.namelist()))
    def test_old_receipts_never_enter_new_package(self):
        archive=self.build()
        with zipfile.ZipFile(archive) as bundle:
            self.assertFalse(any('PRIVATE-SETUP' in name for name in bundle.namelist()))
            self.assertFalse(any(name.startswith('custom-output/') for name in bundle.namelist()))
    def test_recovery_location_and_private_receipts_are_excluded(self):
        for name in ['admin/upgrades/location.php','docs/update-PRIVATE-RECOVERY.txt','docs/backup-PRIVATE-KEY.txt']:
            p=self.root/name;p.parent.mkdir(parents=True,exist_ok=True);p.write_text('PRIVATE-TEST-DATA')
        archive=self.build()
        with zipfile.ZipFile(archive) as bundle:
            for name in bundle.namelist(): self.assertNotIn(b'PRIVATE-TEST-DATA',bundle.read(name))
    def test_symlink_is_excluded(self):
        archive=self.build()
        with zipfile.ZipFile(archive) as bundle: self.assertNotIn('assets/link',bundle.namelist())
    def test_no_setup_code_hash_or_receipt_is_created(self):
        archive=self.build()
        self.assertEqual({p.name for p in self.output.iterdir()}, {archive.name, 'old-PRIVATE-SETUP.txt'})
        with zipfile.ZipFile(archive) as bundle:
            self.assertNotIn('install/access.php',bundle.namelist())
            for name in bundle.namelist(): self.assertNotIn(b'old claim hash',bundle.read(name))
    def test_stale_manifest_is_not_duplicated_or_self_hashed(self):
        (self.root/'docs/PACKAGE-MANIFEST.json').write_text('{"stale":true}')
        archive=self.build()
        with zipfile.ZipFile(archive) as bundle:
            self.assertEqual(bundle.namelist().count('docs/PACKAGE-MANIFEST.json'),1)
            self.assertNotIn('docs/PACKAGE-MANIFEST.json',json.loads(bundle.read('docs/PACKAGE-MANIFEST.json'))['files'])
    def test_manifest_matches_all_payload_files(self):
        archive=self.build()
        with zipfile.ZipFile(archive) as bundle:
            manifest=json.loads(bundle.read('docs/PACKAGE-MANIFEST.json'))
            for name,digest in manifest['files'].items(): self.assertEqual(hashlib.sha256(bundle.read(name)).hexdigest(),digest)
    def test_existing_version_is_not_overwritten(self):
        archive=self.build(); old=archive.read_bytes()
        with self.assertRaises(FileExistsError): self.build()
        self.assertEqual(old,archive.read_bytes())
    def test_output_inside_application_is_refused(self):
        with self.assertRaises(ValueError): packager.package(self.root/'app/output')
    def test_path_traversal_version_is_refused(self):
        (self.root/'VERSION').write_text('../../escape')
        with self.assertRaises(ValueError): self.build()

if __name__=='__main__': unittest.main(verbosity=2)
