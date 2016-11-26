.PHONY: all clean

all: composer bower

composer:
	composer update --no-dev --optimize-autoloader

bower:
	bower update -p && bower prune -p

clean:
	rm -rvf static/bower_components
