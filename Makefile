.PHONY: all composer

all: build composer

build:
	bower install

composer:
	composer update --no-dev --optimize-autoloader
