FROM ridibooks/performance-apache-base:7.1
MAINTAINER Kang Ki Tae <kt.kang@ridi.com>

ENV APACHE_DOC_ROOT /var/www/html/web

ADD . /var/www/html
WORKDIR /var/www/html
RUN make all
