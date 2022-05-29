FROM php:7.4.29-fpm-alpine3.15

COPY --from=mlocati/php-extension-installer:1.5.14 /usr/bin/install-php-extensions /usr/local/bin/

RUN set -eux; \
    rm /usr/local/etc/php-fpm.conf \
       /usr/local/etc/php-fpm.conf.default \
       /usr/local/etc/php/php.ini-development \
       /usr/local/etc/php/php.ini-production \
    ; \
    rm -rf /usr/local/etc/php-fpm.d

RUN set -eux; \
    export \
      XDEBUG_VERSION=3.1.4 \
      COMPOSER_VERSION=2.3.5 \
    ; \
    install-php-extensions \
      xdebug-${XDEBUG_VERSION} \
      intl \
      @composer-${COMPOSER_VERSION} \
    ;

RUN set -eux; \
    composer -n create-project --prefer-dist yiisoft/yii2-app-basic .

COPY ./deploy/data/php/root/ /

