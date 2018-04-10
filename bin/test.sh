#!/usr/bin/env bash

# Run PHPUnit
composer run-script test
PHPUNIT_RESULT=$?
if [ ${PHPUNIT_RESULT} -ne 0 ]; then
    printf '%s\n' 'Unit test failed!' >&2
    exit ${PHPUNIT_RESULT}
fi
