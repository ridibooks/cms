.PHONY: all server client build-thrift

all: server client build-thrift

server:
	make -C server

client:
	composer update --no-dev --optimize-autoloader

build-thrift:
	make -C Thrift thrift

