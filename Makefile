phpcsfixer:
	 php  vendor/bin/php-cs-fixer --no-interaction --allow-risky=yes --dry-run --diff fix
phpcsfixer_fix:
	 php  vendor/bin/php-cs-fixer --no-interaction --allow-risky=yes --ansi fix
unitTest:
	 php  vendor/bin/phpunit
phpstan:
	 php  vendor/bin/phpstan analyse src tests -l 9
psalm:
	 php  ./vendor/bin/psalm --no-cache
messDetector:
	 php vendor/bin/phpmd src text cleancode,codesize,controversial,design,naming,unusedcode --exclude *src/Core/Enums/*
infection:
	 php vendor/bin/infection --min-msi=90 --initial-tests-php-options="-d zend_extension=xdebug.so"

