#!/usr/bin/env bash

curl -X POST \
    -F token=${CI_TRIGGER_TOKEN} \
    -F "ref=master" \
    -F "variables[ENV]=${CI_TRIGGER_ENV}" \
    -F "variables[TARGET]=${CI_TRIGGER_TARGET}" \
    -F "variables[TAG]=${CI_TRIGGER_TAG}" \
    https://gitlab.ridi.io/api/v4/projects/329/trigger/pipeline
