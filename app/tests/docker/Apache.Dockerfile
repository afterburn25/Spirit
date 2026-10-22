# Test-only image: official PHP includes its matching Apache module.
# Image/package provisioning runs at build time, not on the user's shared host.
FROM php:8.4.26-apache-bookworm
RUN apt-get update \
    && apt-get install -y --no-install-recommends python3 python3-pil openssl \
    && rm -rf /var/lib/apt/lists/*
ENV SPIRIT_APACHE_PHP_MODULE=/usr/lib/apache2/modules/libphp.so \
    PYTHONDONTWRITEBYTECODE=1
WORKDIR /app
COPY . /app
USER www-data
CMD ["sh", "-c", "python3 app/tests/apache_access.py && python3 app/tests/layout_routing.py"]
