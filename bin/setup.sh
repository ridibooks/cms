#!/usr/bin/env bash

set -e

mysql -uroot -e 'CREATE DATABASE IF NOT EXISTS cms;'

vendor/bin/phinx migrate -e local
vendor/bin/phinx seed:run -s User -s Menu -s MenuAjax -s UserMenu -e local
