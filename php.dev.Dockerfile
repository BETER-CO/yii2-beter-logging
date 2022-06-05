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

RUN set -eux; \
    export YII2_BETER_LOGGING_VERSION="1.2.0"; \
    export \
      YII2_BETER_LOGGING_URL="https://github.com/BETER-CO/yii2-beter-logging/archive/refs/tags/${YII2_BETER_LOGGING_VERSION}.tar.gz" \
      YII2_BETER_LOGGING_ARCH_DIR="yii2-beter-logging-${YII2_BETER_LOGGING_VERSION}" \
    ; \
    curl -fsSL -o /yii2-beter-logging.tar.gz "$YII2_BETER_LOGGING_URL"; \
    tar -xzf /yii2-beter-logging.tar.gz -C /tmp/; \
    rm /yii2-beter-logging.tar.gz; \
    mv /tmp/${YII2_BETER_LOGGING_ARCH_DIR} /yii2-beter-logging; \
    composer update
