.PHONY: all clean

all: composer bower build-thrift

composer:
	composer update --no-dev --optimize-autoloader

bower:
	bower update -p && bower prune -p

build-thrift:
	make -C Thrift thrift

clean:
	rm -rvf static/bower_components
