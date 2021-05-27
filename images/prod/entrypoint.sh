#!/bin/sh
set -e
if [ -d "/entrypoint.sh.d" ]; then
    for f in /entrypoint.sh.d/*.sh; do
        . "$f"
    done
fi