.PHONY: all composer

all: build composer

build:
	bower install

composer:
	composer install --no-dev --optimize-autoloader
