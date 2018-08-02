#!/usr/bin/env bash
set -e

# Variables
DOCKER_USER=${1}
DOCKER_PASS=${2}
TARGET_IMAGE=${3}
DOCKER_REPO_URI=${4}

DOCKER_TAG=${5:-`git rev-parse --short HEAD`} # default = commit hash
DOCKER_TAG_DEFAULT=latest

function print_usage
{
    echo
    echo "Usage: docker_push.sh <DOCKER_USER> <DOCKER_PASS> <TARGET_IMAGE> <DOCKER_REPO_URI> <DOCKER_TAG>"
    echo
    echo "Example:"
    echo "  docker_push.sh cms:latest ridibooks/cms 1.0.2"
}

function push
{
    docker login -u ${DOCKER_USER} -p ${DOCKER_PASS}

    echo "Tag ${TARGET_IMAGE} with ${DOCKER_REPO_URI}:${DOCKER_TAG_DEFAULT}, ${DOCKER_REPO_URI}:${DOCKER_TAG}"
    docker tag ${TARGET_IMAGE} ${DOCKER_REPO_URI}:${DOCKER_TAG_DEFAULT}
    docker tag ${TARGET_IMAGE} ${DOCKER_REPO_URI}:${DOCKER_TAG}

    echo "Push the image to ${DOCKER_REPO_URI}"
    docker push ${DOCKER_REPO_URI}:${DOCKER_TAG_DEFAULT}
    docker push ${DOCKER_REPO_URI}:${DOCKER_TAG}
}

if [[ -z ${TARGET_IMAGE} ]] || [[ -z `docker images -q ${TARGET_IMAGE}` ]]
then
    echo "TARGET_IMAGE is wrong. Please check the image existing"
    print_usage
    exit 1
fi

if [[ -z ${DOCKER_REPO_URI} ]]
then
    echo "No Docker repository specified."
    print_usage
    exit 1
fi

push
