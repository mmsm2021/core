#!/bin/sh
set -e

if [ -d "/init.sh.d" ]; then
    for f in /init.sh.d/*.sh; do
        . "$f"
    done
fi

if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

exec "$@"