HTTPD_CONTAINER = weekly_dev_httpd
PHP_CONTAINER = weekly_dev_php
REQUIREMENTS = docker docker-compose vi npm node

check:
	$(foreach REQUIREMENT, $(REQUIREMENTS), \
		$(if $(shell command -v $(REQUIREMENT) 2> /dev/null), \
			$(info `$(REQUIREMENT)` has been found. OK!), \
			$(error Please install `$(REQUIREMENT)` before running setup.) \
		) \
	)

setup: check
	cp ./.env.local ./.env
	cp docker-compose.dev.yml docker-compose.override.yml
	vi ./.env
	vi docker-compose.override.yml
	docker-compose up -d --build
	docker exec $(HTTPD_CONTAINER) chmod -R 777 /var/www/weekly-dev/storage
	docker exec $(PHP_CONTAINER) composer install --prefer-dist
	docker exec $(PHP_CONTAINER) php artisan key:generate
	make setup-tables
	make clear-cache

clear-cache:
	docker exec ${PHP_CONTAINER} php artisan optimize:clear
	docker exec ${PHP_CONTAINER} php artisan optimize
	docker exec ${PHP_CONTAINER} php artisan cache:clear
	docker exec ${PHP_CONTAINER} php artisan config:clear
	docker exec ${PHP_CONTAINER} php artisan route:clear
	docker exec ${PHP_CONTAINER} php artisan view:clear

migrate:
	docker exec ${PHP_CONTAINER} php artisan migrate

seed:
	docker exec ${PHP_CONTAINER} php artisan db:seed --class=DatabaseSeeder

update-setup:
	cp docker-compose.dev.yml docker-compose.override.yml
	docker-compose up -d --build

remove-setup:
	docker-compose down

setup-tables:
	docker exec $(PHP_CONTAINER) php artisan migrate:fresh --seed

check-code:
	make check-codesniffer
	make check-phpstan
	make check-md

check-codesniffer:
	docker exec $(PHP_CONTAINER) composer cs-check-code

check-phpstan:
	docker exec $(PHP_CONTAINER) composer phpstan

check-md:
	docker exec $(PHP_CONTAINER) composer md

fix-code-standard:
	docker exec $(PHP_CONTAINER) vendor/bin/phpcbf --standard=PSR12 app/

bash:
	docker exec -it $(PHP_CONTAINER) bash

setup-test:
	docker exec ${PHP_CONTAINER} php artisan migrate --env=testing
	make seed-test

seed-test:
	docker exec ${PHP_CONTAINER} php artisan db:seed --class=ShopsSeeder --env=testing
