FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends libcurl4-openssl-dev ca-certificates \
    && docker-php-ext-install curl \
    && rm -rf /var/lib/apt/lists/*

# Módulos e endurecimento do Apache/PHP
RUN a2enmod rewrite headers expires deflate \
    && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf \
    && printf 'ServerTokens Prod\nServerSignature Off\nTraceEnable Off\n' > /etc/apache2/conf-available/hardening.conf \
    && a2enconf hardening \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf 'expose_php=Off\ndisplay_errors=Off\nlog_errors=On\nsession.cookie_httponly=1\nsession.use_strict_mode=1\nupload_max_filesize=2M\npost_max_size=4M\n' \
       > "$PHP_INI_DIR/conf.d/zz-app.ini"

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && chmod 775 /var/www/html/data \
    && sed -i 's/\r$//' /var/www/html/start.sh \
    && chmod +x /var/www/html/start.sh

EXPOSE 80
CMD ["/var/www/html/start.sh"]
