# Private storage policy — 0.1.0-dev.2

Never infer account-wide privacy from the parent of DOCUMENT_ROOT alone: an addon root may live below the primary public_html. Runtime now lives in a per-installation `.spirit-<hash>` directory under a validated account-level base.

Selection order: an explicitly host-configured SPIRIT_ACCOUNT_HOME, otherwise the effective PHP user's POSIX account home, then a host-provided HOME containing this application. No request parameter or HTTP header supplies a filesystem path. An explicit host-managed private mount can be outside the application hierarchy. All candidates must be canonical existing directories, not symlinks or filesystem root.

Known public roots include DOCUMENT_ROOT, CONTEXT_DOCUMENT_ROOT, conventional public-directory ancestors/children, and the optional host-configured SPIRIT_PUBLIC_ROOTS (colon-separated absolute paths on Unix). A candidate within a known public root is rejected. If account discovery is ambiguous, stop instead of storing secrets in the site. Additional server aliases cannot be enumerated reliably by PHP: the hosting deployment check must confirm them or provide the explicit list. This is not an assertion that every provider layout is automatically verified.

PHP creates the private runtime and children with 0700, writes configuration with verified 0600 permissions BEFORE secret bytes, and tests actual IO in each child. Defensive deny-all .htaccess files are written to private directories. These are defense in depth, not a substitute for outside-public placement or equivalent rules on other servers. No chmod 0777 or assumption that PHP can bypass host ownership/ACL/open_basedir restrictions.

For a compatible account with usable home information, no manual runtime/config folder creation is needed. For an atypical provider, SPIRIT_ACCOUNT_HOME must be set in server/hosting configuration, not a public form. Do not expose secrets to make an installation succeed.

## Existing dev.1 installations
This version changes runtime placement for nested document roots. Do not run setup to replace an existing database. Before upgrading an existing development installation, preserve the original `.spirit-<hash>` runtime, stop requests, and move it intact to the new verified account-private base using the hosting file manager. Retain its config, secret, completion marker, sessions, media and database prefix. Migration automation is not implemented; no in-place upgrade package is claimed. Never delete the old state to bypass a prefix collision. Runtime move and real-host acceptance still require staging validation.

## Regression coverage
26 non-root storage assertions cover addon placement, known/explicit public roots, invalid/symlink/dot-segment paths, untrusted HTTP headers, private child IO and permissions, access rules, atomic writes, independent config/marker locks, broken links, unknown installer files, unreadable installer folders, read-only writes and parent-directory deletion restrictions. Run `php tests/storage.php`.
