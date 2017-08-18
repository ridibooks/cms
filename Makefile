.PHONY: all composer

all: build composer

build:
	bower install --allow-root

composer:
	composer install --no-dev --optimize-autoloader
