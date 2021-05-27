#!/bin/sh
set -e

if [ -d "/init.sh.d" ]; then
    for f in /init.sh.d/*.sh; do
        . "$f"
    done
fi