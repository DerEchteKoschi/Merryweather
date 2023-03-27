#!/usr/bin/env sh
export XDEBUG_MODE=off
echo cs-fixer
echo --------
FILE=tools/php-cs-fixer/vendor
if [ ! -d "$FILE" ]; then
    composer install --working-dir=tools/php-cs-fixer
fi
tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src
echo
echo phpstan
echo -------
FILE=tools/phpstan/vendor
if [ ! -d "$FILE" ]; then
    composer install --working-dir=tools/phpstan
fi
tools/phpstan/vendor/bin/phpstan analyse -c phpstan.neon --xdebug
