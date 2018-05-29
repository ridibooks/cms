#!/usr/bin/env bash

set -e

export PHINX_DBHOST=${PHINX_DBHOST:-127.0.0.1}
export PHINX_DBNAME=${PHINX_DBNAME:-cms}
export PHINX_DBUSER=${PHINX_DBUSER:-root}
export PHINX_DBPASS=${PHINX_DBPASS:-""}

if [[ -z ${PHINX_DBPASS} ]]; then
    mysql -h${PHINX_DBHOST} -u${PHINX_DBUSER} -e "CREATE DATABASE IF NOT EXISTS ${PHINX_DBNAME};"
else
    mysql -h${PHINX_DBHOST} -u${PHINX_DBUSER} -p${PHINX_DBPASS} -e "CREATE DATABASE IF NOT EXISTS ${PHINX_DBNAME};"
fi

vendor/bin/phinx rollback -t 0 -e phinx_env
vendor/bin/phinx migrate -e phinx_env
vendor/bin/phinx seed:run -s User -s Menu -s MenuAjax -s Tag -s TagMenu -s UserTag -s Group -s GroupTag -s GroupUser -e phinx_env
