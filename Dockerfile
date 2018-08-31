FROM ktkang/node-composer:1.0.1 AS builder

RUN apk --no-cache add \
    bash \
    php7-dom \
    php7-pdo \
    php7-pdo_mysql \
&& rm -rf /var/cache/apk/* \
&& npm install -g bower

WORKDIR /build

# Copy commands
COPY bin/build.sh bin/init_db.sh bin/run_test.sh /usr/local/bin/
ENTRYPOINT ["/bin/bash", "-c"]
CMD ["build.sh", "dev"]

# Copy package manifest Files
COPY bower.json .bowerrc composer.json composer.lock ./

# Run build
RUN build.sh prod


FROM ridibooks/performance-apache-base:7.1
MAINTAINER Kang Ki Tae <kt.kang@ridi.com>

ENV APACHE_DOC_ROOT /var/www/html/web

COPY --from=builder /build ./
COPY . ./
