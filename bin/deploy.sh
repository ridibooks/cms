#!/usr/bin/env bash

curl -X POST \
    -F token=${CI_TRIGGER_TOKEN} \
    -F "ref=master" \
    -F "variables[ENV]=${CI_TRIGGER_ENV}" \
    -F "variables[TARGET]=${CI_TRIGGER_TARGET}" \
    https://gitlab.ridi.io/api/v4/projects/329/trigger/pipeline
