ARG PHP_FPM_IMAGE=php:8.1-fpm-alpine3.16
ARG COMPOSER_IMAGE=composer:2.5

FROM $PHP_FPM_IMAGE AS builder

RUN set -e \
    # Install dependencies \
    && apk update \
    && apk upgrade \
    && apk add --update --no-cache autoconf automake bash bash-completion make gcc g++ libtool pkgconfig icu-dev
#    && docker-php-ext-enable xdebug
    # Enable Xdebug \
RUN export CFLAGS="$PHP_CFLAGS" CPPFLAGS="$PHP_CPPFLAGS" LDFLAGS="$PHP_LDFLAGS" \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        opcache \
        mysqli pdo pdo_mysql intl
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS
RUN apk add --update linux-headers
RUN pecl install xdebug-3.1.5
RUN docker-php-ext-enable xdebug
RUN apk del -f .build-deps

# Configure Xdebug
#RUN echo "xdebug.log=/usr/src/var/log/xdebug.log" >> /usr/local/etc/php/conf.d/xdebug.ini
# base image
FROM $PHP_FPM_IMAGE AS base

ENV PHP_LIB_DIR=/usr/local/lib/php
ENV NODE_VERSION 19.3.0

COPY php.ini $PHP_INI_DIR/
COPY --from=builder $PHP_LIB_DIR/extensions/ $PHP_LIB_DIR/extensions/
COPY --from=builder $PHP_INI_DIR/conf.d/ $PHP_INI_DIR/conf.d/
RUN apk add icu-dev
ARG MYUSER=app
ARG MYGROUP=app
ARG MYUID=501
ARG MYGID=501

RUN set -e \
    # Add runtime user
    && addgroup -g $MYGID -S $MYGROUP  \
    && adduser -u $MYUID -S -D -G $MYGROUP $MYUSER

ENV APP_DIR=/usr/src
WORKDIR $APP_DIR/
RUN chown $MYUSER:$MYGROUP $APP_DIR/

RUN mkdir $APP_DIR/var && chown $MYUSER:$MYGROUP $APP_DIR/var

USER $MYUSER

ENV PHP_DISPLAY_ERRORS=0
ENV PHP_EXPOSE=0
ENV PHP_MEMORY_LIMIT=128M

ENV XDEBUG_MODE=off

EXPOSE 9000
CMD ["php-fpm", "--nodaemonize"]


# development dependencies
FROM $COMPOSER_IMAGE AS composer
# development/test/ci image
FROM base AS development

COPY --from=composer /usr/bin/composer /usr/local/bin/
USER root

RUN apk add mc

ENV PHP_DISPLAY_ERRORS=1
ENV PHP_EXPOSE=1


# production image
FROM base AS production

ARG APP_ENV=prod
ENV APP_ENV=$APP_ENV
ENV APP_DEBUG=0

COPY --chown=app:app bin/ $APP_DIR/bin/
COPY --chown=app:app config/ $APP_DIR/config/
COPY --chown=app:app public/ $APP_DIR/public/
COPY --chown=app:app src/ $APP_DIR/src/
COPY --chown=app:app templates/ $APP_DIR/templates/
COPY --chown=app:app translations/ $APP_DIR/translations/
COPY --chown=app:app migrations/ $APP_DIR/migrations/
COPY --chown=app:app vendor/ $APP_DIR/vendor/
COPY --chown=app:app composer.json $APP_DIR/composer.json

ENV PHP_OPCACHE_REVALIDATE_FREQ=0
