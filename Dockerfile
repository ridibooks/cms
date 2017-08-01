FROM ridibooks/performance-apache-base:latest
MAINTAINER Kang Ki Tae <kt.kang@ridi.com>

ADD . /var/www/html
WORKDIR /var/www/html
RUN make all
