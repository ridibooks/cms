.PHONY: all build composer phinx env-dev

all: build composer

build:
	bower install --allow-root

composer:
	composer install --no-dev --optimize-autoloader

phinx:
	vendor/bin/phinx migrate
	vendor/bin/phinx seed:run

env-dev:
	echo "DEBUG=1" > .env
	echo "TEST_ID=admin" >> .env
