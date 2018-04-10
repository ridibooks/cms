#!/usr/bin/env bash
set -e

DOCKER_TAG=${DOCKER_TAG:-latest}
DEFAULT_TAG=$(git rev-parse --short HEAD) # commit hash

docker login -u ${DOCKER_USER} -p ${DOCKER_PASS}

echo "Bulilding ridibooks/cms..."
docker build -t ridibooks/cms:${DEFAULT_TAG} .
echo "Builded ridibooks/cms"

echo "Pushing ridibooks/cms..."
docker tag ridibooks/cms:${DEFAULT_TAG} ridibooks/cms:${DOCKER_TAG}
docker push ridibooks/cms
echo "Pushed ridibooks/cms"
