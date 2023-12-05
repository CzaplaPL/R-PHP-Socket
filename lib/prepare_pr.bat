php  vendor/bin/php-cs-fixer --no-interaction --allow-risky=yes --ansi fix
php  vendor/bin/phpstan analyse
php ./vendor/bin/psalm --no-cache
php vendor/bin/phpmd src html  ruleset.xml --reportfile phpmdReport.html
php vendor/bin/phpunit --coverage-text --coverage-html  coverage
php vendor/bin/infection
php vendor/bin/churn run src