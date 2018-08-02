FROM ktkang/node-composer:1.0.1 AS builder

RUN npm install -g bower

WORKDIR /build

# Instll Bower modules
COPY bower.json .bowerrc ./
RUN bower install --allow-root \
&& bower prune -p --allow-root

# Install Composer packages
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --ignore-platform-reqs



FROM ridibooks/performance-apache-base:7.1
MAINTAINER Kang Ki Tae <kt.kang@ridi.com>

ENV APACHE_DOC_ROOT /var/www/html/web

COPY --from=builder /build ./
COPY . ./
