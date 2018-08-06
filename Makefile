.PHONY: help build up down test db log push deploy deploy-dev

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(lastword $(MAKEFILE_LIST)) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

# Common environment variables
export LOCAL_DOCKER_REPO ?= cms
export LOCAL_DOCKER_TAG ?= latest

build: ## Build Docker image.
	docker-compose build --force-rm

up: ## Run Docker containers.
	docker-compose up -d

down: ## Clean Docker containers, networks, and volumes.
	docker-compose down

test: ## Test Docker image.
	docker-compose run --rm builder run_test

db: ## Initialize DB schema.
	docker-compose run --rm builder init_db

log: ## View Docker logs.
	docker-compose logs -f

push: ## Push image to Docker repo
	bin/docker_push.sh ${DOCKER_USER} ${DOCKER_PASS} \
		"${LOCAL_DOCKER_REPO}:${LOCAL_DOCKER_TAG}" ridibooks/cms ${TRAVIS_TAG}

deploy: ## Trigger CI pipeline for deploying (production)
	bin/deploy prod ${CI_TRIGGER_TOKEN} ${TRAVIS_TAG}

deploy-dev: ## Trigger CI pipeline for deploying (development)
	bin/deploy dev ${CI_TRIGGER_TOKEN} ${TRAVIS_BRANCH}
