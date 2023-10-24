#!/bin/sh

while ! nc -z ec-cube 80; do sleep 1; done; while ! nc -z chrome 4444; do sleep 1; done; ls -la && php bin/console cache:clear --no-warmup && /var/www/html/vendor/bin/codecept run -d acceptance --env chrome-headless,local -g customer

