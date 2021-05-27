FROM haproxy:1.9.6-alpine as base

FROM base as dev
COPY proxy/haproxy.dev.cfg /usr/local/etc/haproxy/haproxy.cfg

FROM base as prod
COPY proxy/haproxy.prod.cfg /usr/local/etc/haproxy/haproxy.cfg
