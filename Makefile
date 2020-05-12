#!/bin/bash

.PHONY: help all tests clean

help:
	@echo "\033[32mmake up \033[0m- up containers"
	@echo "\033[32mmake install \033[0m- up containers and install all dependencies"
	@echo "\033[32mmake php \033[0m- go into php container"
	@echo "\033[32mmake perm \033[0m- fix permissions (for example after composer install)"
	@echo "\033[32mmake cache \033[0m- clear cache (for dev and test environments)"
	@echo "\033[32mmake composer \033[0m- install composer libraries"
	@echo "\033[32mmake db \033[0m- drop and create databases (for dev and test environments)"
	@echo "\033[32mmake diff \033[0m- calculate doctrine diff"
	@echo "\033[32mmake migration \033[0m- execute doctrine migration for dev and test envs"
	@echo "\033[32mmake tests \033[0m- run all tests, or one particular test. Example: 'make tests file=src/path/to/test.php method=test_name'"

up:
	@echo "\033[32mStarting containers...\033[0m"
	@docker-compose up -d

	@echo "\033[32mSetting permissions...\033[0m"
	@docker-compose exec php chown -R www-data:www-data ./
	@docker-compose exec postgres chown -R postgres:postgres /var/lib/postgresql/data
	@docker-compose exec postgres_test chown -R postgres:postgres /var/lib/postgresql/data

	@echo "\033[32mApply migrations...\033[0m"
	@docker-compose exec -T php /var/www/socialtech/bin/console doctrine:migrations:migrate --allow-no-migration --all-or-nothing  -n
	@docker-compose exec -T php /var/www/socialtech/bin/console doctrine:migrations:migrate --allow-no-migration --all-or-nothing  -n --env=test

	@echo "\033[33mDone \033[0m"

install:
	@echo "\033[32mInstalling all...\033[0m"

	@echo "\033[33mUp containers...\033[0m"
	@docker-compose up -d --build --force-recreate

	@echo "\033[32mStop supervisor...\033[0m"
	@docker-compose exec -T php service supervisor stop

	@echo "\033[33mUpdate composer...\033[0m"
	@docker-compose exec -T php composer self-update
	@docker-compose exec -T php composer install
	@docker-compose exec php chown -R www-data:www-data ./
	@docker-compose exec postgres chown -R postgres:postgres /var/lib/postgresql/data
	@docker-compose exec postgres_test chown -R postgres:postgres /var/lib/postgresql/data

	@echo "\033[32mApply migrations...\033[0m"
	@docker-compose exec -T php /var/www/socialtech/bin/console doctrine:migrations:migrate --allow-no-migration --all-or-nothing  -n
	@docker-compose exec -T php /var/www/socialtech/bin/console doctrine:migrations:migrate --allow-no-migration --all-or-nothing  -n --env=test

	@echo "\033[32mRestart supervisor...\033[0m"
	@docker-compose exec -T php service supervisor start

	@echo "\033[33mDone \033[0m"

php:
	@echo "\033[32mEntering into php container...\033[0m"
	@docker-compose exec php bash

perm:
	@echo "\033[32mSettting permissions...\033[0m"
	@docker-compose exec php chown -R www-data:www-data ./
	@docker-compose exec postgres chown -R postgres:postgres /var/lib/postgresql/data
	@docker-compose exec postgres_test chown -R postgres:postgres /var/lib/postgresql/data

cache:
	@echo "\033[32mCleaning cache for dev and test enironments...\033[0m"
	@docker-compose exec -T php bin/console cache:clear
	@docker-compose exec -T php bin/console cache:clear --env=test

db:
	@echo "\033[32mRecreating database for dev and test enironments...\033[0m"
	@docker-compose exec -T php bin/console doctrine:database:drop --force --env=dev
	@docker-compose exec -T php bin/console doctrine:database:create --env=dev
	@docker-compose exec -T php bin/console doctrine:migrations:migrate --env=dev
	@docker-compose exec -T php bin/console doctrine:database:drop --force --env=test
	@docker-compose exec -T php bin/console doctrine:database:create --env=test
	@docker-compose exec -T php bin/console doctrine:migrations:migrate --env=test

diff:
	@echo "\033[32mCalculating Doctrine diff...\033[0m"
	@docker-compose exec -T php bin/console doctrine:migrations:diff

composer:
	@echo "\033[32mUpdating composer libraries\033[0m"
	@docker-compose exec -T php composer install


migration:
	@echo "\033[32mExecuting Doctrine migrations for dev and test environments...\033[0m"
	@docker-compose exec -T php bin/console doctrine:migrations:migrate
	@docker-compose exec -T php bin/console doctrine:migrations:migrate --env=test

tests:
	@echo Running test $(file)
	@docker-compose exec -T php bin/phpunit $(file) --filter "$(method)"
	@echo "\033[33mDone \033[0m"
