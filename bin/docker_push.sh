#!/usr/bin/env bash
set -e

DOCKER_TAG=${TRAVIS_TAG:-latest}
COMMIT=${TRAVIS_COMMIT::8}

docker login -u ${DOCKER_USER} -p ${DOCKER_PASS}
docker build -t ridibooks/cms:${COMMIT} .

echo "Pushing ridibooks/cms"
docker tag ridibooks/cms:${COMMIT} ridibooks/cms:${DOCKER_TAG}
docker push ridibooks/cms
echo "Pushed ridibooks/cms"
