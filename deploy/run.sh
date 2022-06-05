#!/usr/bin/env bash

DOCKER_COMPOSE_FILE="docker-compose.dev.yml"
NGINX_SERVICE_NAME="yii2-beter-logging-nginx"
PHP_SERVICE_NAME="yii2-beter-logging-php"

set -e

# Styles
IC="\e[34m" # - info
SC="\e[32m" # - success
EC="\e[31m" # - error
AC="\e[33m" # - alert
CC="\e[0m"  #  - END (marker)

function help() {
  echo -e "${IC}Commands:${CC}"
  echo -e "  ${SC}help              ${CC}- Show all information about available commands."
  echo -e "  ${SC}reload.nginx      ${CC}- Send reload signal to nginx"
  echo -e "  ${SC}reload.php-fpm    ${CC}- Send reload signal to php-fpm"
  echo -e "  ${SC}lastmodified      ${CC}- Show last modified php files"
}

up() {
  docker-compose up -d
}

reload.nginx() {
  docker-compose -f ${DOCKER_COMPOSE_FILE} exec ${NGINX_SERVICE_NAME} nginx -s reload
}

reload.php-fpm() {
  docker-compose -f ${DOCKER_COMPOSE_FILE} exec ${PHP_SERVICE_NAME} sh -c 'kill -USR2 `cat /usr/local/var/run/php-fpm.pid`'
}

lastmodified() {
  docker-compose -f ${DOCKER_COMPOSE_FILE} exec ${PHP_SERVICE_NAME} sh -c "find /var/www/html -not -path '*/runtime/*' -not -path '*/web/assets/*' -not -path '*/vendor/*' -exec stat -c '%Y %n' '{}' + | sort -n"
}

if [ "$(type -t $1)" = "function" ]; then
  $@
else
  help
fi
