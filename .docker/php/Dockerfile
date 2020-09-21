FROM php:7.4-fpm-alpine

ENV XDEBUG_VERSION=2.9.6 \
    COMPOSER_ALLOW_SUPERUSER=1

RUN apk --no-cache add \
        postgresql-dev \
        oniguruma-dev \
        icu-dev \
        gettext-dev \
        g++ \
        autoconf \
        make \
        git && \
    docker-php-ext-install \
        mbstring \
        opcache \
        gettext \
        intl \
        pdo_pgsql \
        > /dev/null && \
    pecl install xdebug-$XDEBUG_VERSION > /dev/null && \
        docker-php-ext-enable xdebug > /dev/null && \
        apk del g++ autoconf make && \
        rm -r /tmp/pear/* && \
        echo -e "xdebug.remote_port = 9001\n\
xdebug.idekey = \"PHPSTORM\"\n\
xdebug.remote_enable=on\n\
xdebug.remote_connect_back=0\n\
" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN curl -sS https://getcomposer.org/installer | php -- --version=2.0.0-alpha3 --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www
COPY . /var/www/

RUN composer install --prefer-dist --no-progress

RUN addgroup -g 1000 appuser && \
    adduser -D -u 1000 -G appuser appuser && \
    chown -R appuser:appuser .
