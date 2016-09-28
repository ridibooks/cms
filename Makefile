.PHONY: all clean

all: composer bower

composer:
	@php ../../bin/composer.phar update --no-dev

bower:
	@cd static && bower update -p && bower prune -p

clean:
	rm -rvf static/bower_components
