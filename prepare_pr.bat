php  vendor/bin/php-cs-fixer --no-interaction --allow-risky=yes --ansi fix
php  vendor/bin/phpstan analyse src tests -l 9
php ./vendor/bin/psalm --no-cache
php vendor\bin\phpunit --coverage-text --coverage-html  coverage
infection