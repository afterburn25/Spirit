# Real Apache verification environment

The actual shipped-rule test passed32 assertions locally using non-root Apache/PHP8.4 and verified loopback HTTPS. The first CI provisioning step failed before tests because Ubuntu22.04 did not provide libapache2-mod-php8.4 through its configured APT sources. This was not an application access-rule test failure.

The separate Apache CI job now uses the official version-pinned php:8.4.26-apache-bookworm image, which includes its matching Apache module. Python/OpenSSL are installed only while building this disposable test image. Runtime executes as www-data with a read-only root filesystem, private /tmp, no external networking, and a temporary loopback HTTPS listener. No host deployment, production account or live provider is accessed; the existing database/browser matrix is unchanged.

Image reference verified from docker-library/official-images/library/php and the official Docker Hub PHP image documentation on2026-10-14. The runtime is not claimed verified until the exact new Apache job passes. This remains test infrastructure, not a Docker requirement for the user's shared hosting.

Sources: https://github.com/docker-library/official-images/blob/master/library/php and https://hub.docker.com/_/php .
