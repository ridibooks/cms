.PHONY: all clean

all: composer bower build

composer:
	@php ../../bin/composer.phar update --no-dev

bower:
	@cd static && bower update -p && bower prune -p

build:
	cd static && npm install && npm update && npm run build

clean:
	rm -rvf static/bower_components
