#!/bin/sh
php ./vendor/bin/doctrine-migrations up-to-date || php ./vendor/bin/doctrine-migrations migrate --no-interaction || { echo "Failed to run migrations" && exit 1; };
echo "Migrations.sh was successful";