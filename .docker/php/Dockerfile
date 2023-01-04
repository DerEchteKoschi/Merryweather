ARG PHP_FPM_IMAGE=php:8.1-fpm-alpine3.16
ARG COMPOSER_IMAGE=composer:2.5

FROM $PHP_FPM_IMAGE AS builder

RUN set -e \
    # Install dependencies \
    && apk update \
    && apk upgrade \
    && apk add --update --no-cache autoconf automake bash bash-completion make gcc g++ libtool pkgconfig php8-pecl-xdebug
    # Enable Xdebug
RUN export CFLAGS="$PHP_CFLAGS" CPPFLAGS="$PHP_CPPFLAGS" LDFLAGS="$PHP_LDFLAGS" \
    && docker-php-ext-install -j$(nproc) \
        opcache \
        mysqli pdo pdo_mysql


# base image
FROM $PHP_FPM_IMAGE AS base

ENV PHP_LIB_DIR=/usr/local/lib/php
ENV NODE_VERSION 19.3.0

COPY php.ini $PHP_INI_DIR/
COPY --from=builder $PHP_LIB_DIR/extensions/ $PHP_LIB_DIR/extensions/
COPY --from=builder $PHP_INI_DIR/conf.d/ $PHP_INI_DIR/conf.d/

ARG MYUSER=app
ARG MYGROUP=app
ARG MYUID=501
ARG MYGID=501

RUN set -e \
    # Add runtime user
    && addgroup -g $MYGID -S $MYGROUP  \
    && adduser -u $MYUID -S -D -G $MYGROUP $MYUSER

RUN addgroup -g 1000 node \
    && adduser -u 1000 -G node -s /bin/sh -D node \
    && apk add --no-cache \
        libstdc++ \
    && apk add --no-cache --virtual .build-deps \
        curl \
    && ARCH= && alpineArch="$(apk --print-arch)" \
      && case "${alpineArch##*-}" in \
        x86_64) \
          ARCH='x64' \
          CHECKSUM="0964f45fb6040e6bc04f75dcd350412c75a15940d12b14ed7c445397eb15daa4" \
          ;; \
        *) ;; \
      esac \
  && if [ -n "${CHECKSUM}" ]; then \
    set -eu; \
    curl -fsSLO --compressed "https://unofficial-builds.nodejs.org/download/release/v$NODE_VERSION/node-v$NODE_VERSION-linux-$ARCH-musl.tar.xz"; \
    echo "$CHECKSUM  node-v$NODE_VERSION-linux-$ARCH-musl.tar.xz" | sha256sum -c - \
      && tar -xJf "node-v$NODE_VERSION-linux-$ARCH-musl.tar.xz" -C /usr/local --strip-components=1 --no-same-owner \
      && ln -s /usr/local/bin/node /usr/local/bin/nodejs; \
  else \
    echo "Building from source" \
    # backup build
    && apk add --no-cache --virtual .build-deps-full \
        binutils-gold \
        g++ \
        gcc \
        gnupg \
        libgcc \
        linux-headers \
        make \
        python3 \
    # gpg keys listed at https://github.com/nodejs/node#release-keys
    && for key in \
      4ED778F539E3634C779C87C6D7062848A1AB005C \
      141F07595B7B3FFE74309A937405533BE57C7D57 \
      74F12602B6F1C4E913FAA37AD3A89613643B6201 \
      61FC681DFB92A079F1685E77973F295594EC4689 \
      8FCCA13FEF1D0C2E91008E09770F7A9A5AE15600 \
      C4F0DFFF4E8C1A8236409D08E73BC641CC11F4C8 \
      890C08DB8579162FEE0DF9DB8BEAB4DFCF555EF4 \
      C82FA3AE1CBEDC6BE46B9360C43CEC45C17AB93C \
      108F52B48DB57BB0CC439B2997B01419BD92F80A \
    ; do \
      gpg --batch --keyserver hkps://keys.openpgp.org --recv-keys "$key" || \
      gpg --batch --keyserver keyserver.ubuntu.com --recv-keys "$key" ; \
    done \
    && curl -fsSLO --compressed "https://nodejs.org/dist/v$NODE_VERSION/node-v$NODE_VERSION.tar.xz" \
    && curl -fsSLO --compressed "https://nodejs.org/dist/v$NODE_VERSION/SHASUMS256.txt.asc" \
    && gpg --batch --decrypt --output SHASUMS256.txt SHASUMS256.txt.asc \
    && grep " node-v$NODE_VERSION.tar.xz\$" SHASUMS256.txt | sha256sum -c - \
    && tar -xf "node-v$NODE_VERSION.tar.xz" \
    && cd "node-v$NODE_VERSION" \
    && ./configure \
    && make -j$(getconf _NPROCESSORS_ONLN) V= \
    && make install \
    && apk del .build-deps-full \
    && cd .. \
    && rm -Rf "node-v$NODE_VERSION" \
    && rm "node-v$NODE_VERSION.tar.xz" SHASUMS256.txt.asc SHASUMS256.txt; \
  fi \
  && rm -f "node-v$NODE_VERSION-linux-$ARCH-musl.tar.xz" \
  && apk del .build-deps \
  # smoke tests
  && node --version \
  && npm --version

ENV YARN_VERSION 1.22.19

RUN apk add --no-cache --virtual .build-deps-yarn curl gnupg tar \
  && for key in \
    6A010C5166006599AA17F08146C2130DFD2497F5 \
  ; do \
    gpg --batch --keyserver hkps://keys.openpgp.org --recv-keys "$key" || \
    gpg --batch --keyserver keyserver.ubuntu.com --recv-keys "$key" ; \
  done \
  && curl -fsSLO --compressed "https://yarnpkg.com/downloads/$YARN_VERSION/yarn-v$YARN_VERSION.tar.gz" \
  && curl -fsSLO --compressed "https://yarnpkg.com/downloads/$YARN_VERSION/yarn-v$YARN_VERSION.tar.gz.asc" \
  && gpg --batch --verify yarn-v$YARN_VERSION.tar.gz.asc yarn-v$YARN_VERSION.tar.gz \
  && mkdir -p /opt \
  && tar -xzf yarn-v$YARN_VERSION.tar.gz -C /opt/ \
  && ln -s /opt/yarn-v$YARN_VERSION/bin/yarn /usr/local/bin/yarn \
  && ln -s /opt/yarn-v$YARN_VERSION/bin/yarnpkg /usr/local/bin/yarnpkg \
  && rm yarn-v$YARN_VERSION.tar.gz.asc yarn-v$YARN_VERSION.tar.gz \
  && apk del .build-deps-yarn \
  # smoke test
  && yarn --version
RUN apk add mc

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
