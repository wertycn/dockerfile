#!/usr/bin/env sh
set -e

crond
php-fpm -D
nginx -g 'daemon off;'