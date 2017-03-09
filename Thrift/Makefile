.PHONY: clean all

all: thrift doc

thrift:
	thrift -r --gen php:server,psr4 thrift-src/AdminMenu.thrift
	thrift -r --gen php:server,psr4 thrift-src/AdminTag.thrift
	thrift -r --gen php:server,psr4 thrift-src/AdminUser.thrift
	cp -r gen-php/Ridibooks/Cms/Thrift/* ./src/
	rm -rf gen-php

doc:
	mkdir -p docs
	thrift -r --gen html:standalone -out docs thrift-src/AdminMenu.thrift
	thrift -r --gen html:standalone -out docs thrift-src/AdminTag.thrift
	thrift -r --gen html:standalone -out docs thrift-src/AdminUser.thrift

clean:
	rm -rf gen-* dist-* docs
