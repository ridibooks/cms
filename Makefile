.PHONY: all dev build composer composer-dev clean

all: build composer

dev: build compose-dev

build:
	bower install --allow-root

composer:
	composer install --no-dev --optimize-autoloader

compose-dev:
	composer install

init-db:
	bin/setup.sh

clean:
	rm -rf vendor
	rm -rf web/static/bower_components
	rm .env
