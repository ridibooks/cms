FROM haproxy:1.9.6-alpine as base

FROM base as dev
COPY config/haproxy.dev.cfg /usr/local/etc/haproxy/haproxy.cfg

FROM base as prod
COPY config/haproxy.prod.cfg /usr/local/etc/haproxy/haproxy.cfg