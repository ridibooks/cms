#!/usr/bin/env bash

set -e

# See Phinx configuration from phinx.yml
vendor/bin/phinx migrate -v
vendor/bin/phinx seed:run -v
