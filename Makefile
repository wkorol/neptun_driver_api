SHELL := /bin/bash
DOCKER_COMPOSE ?= docker compose
EXEC_COMMAND ?= ${DOCKER_COMPOSE} exec application
PHPUNIT_TEST_PATH ?= tests/
COMPOSER_EXEC ?= composer
INSTALL_COMPOSER_COMMAND ?= ${EXEC_COMMAND} ${COMPOSER_EXEC} install

start: build copy_env_vars create_volumes up composer_install generate_jwt_key migration load_data
create_volumes: # Create docker volumes
	docker volume create --name=neptun-postgresql || true
copy_env_vars: # copy default env vars
	cp -n .env.local.example .env || true
build: # Build docker compose
	${DOCKER_COMPOSE} build
up: # Start docker compose (in the background)
	${DOCKER_COMPOSE} up -d
composer_install: #install composer on docker container (use EXEC_COMMAND to run without docker compose)
	${INSTALL_COMPOSER_COMMAND}
phpstan:  # run phpstan linter (use EXEC_COMMAND to run without docker compose)
	${EXEC_COMMAND} php -d memory_limit=-1 -d xdebug.mode=off vendor/bin/phpstan analyse -vvv --no-progress --memory-limit=-1
phpcsfixer:  # automatically fix php code style using phpcsfixer (use EXEC_COMMAND to run without docker compose)
	${EXEC_COMMAND} php -d memory_limit=-1 -d xdebug.mode=off vendor/bin/php-cs-fixer fix -vvv --show-progress=dots
bash: # access to bash on docker container (use EXEC_COMMAND to run without docker compose)
	${EXEC_COMMAND} bash
migration: # run migrations (use EXEC_COMMAND to run without docker compose)
	${EXEC_COMMAND} php bin/console doctrine:migrations:migrate -n
load_data: # load data to local postgres from inserts.sql
	${DOCKER_COMPOSE} exec -T database psql \
		-U main \
		-d main \
		-f - < inserts.sql
generate_jwt_key:
	${EXEC_COMMAND} php bin/console lexik:jwt:generate-keypair --overwrite --no-interaction
