.PHONY: clean all

all: thrift doc

thrift:
	thrift -r --gen php:server,psr4 src/AdminMenu.thrift
	thrift -r --gen php:server,psr4 src/AdminTag.thrift
	thrift -r --gen php:server,psr4 src/AdminUser.thrift
	cp -r gen-php/Ridibooks/CmsServer/Thrift/* ../src/CmsServer/Thrift/
	cp -r gen-php/Ridibooks/CmsServer/Thrift/* ../src/Cms/Thrift/
	rm -rf gen-php

doc:
	mkdir -p docs
	thrift -r --gen html:standalone -out docs src/AdminMenu.thrift
	thrift -r --gen html:standalone -out docs src/AdminTag.thrift
	thrift -r --gen html:standalone -out docs src/AdminUser.thrift

clean:
	rm -rf gen-* dist-* docs
