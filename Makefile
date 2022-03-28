phpcsfixer:
	 php  vendor/bin/php-cs-fixer --no-interaction --allow-risky=yes --dry-run --diff fix
phpcsfixer_fix:
	 php  vendor/bin/php-cs-fixer --no-interaction --allow-risky=yes --ansi fix
unitTest:
    php vendor/bin/phpunit