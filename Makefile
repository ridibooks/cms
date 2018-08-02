.PHONY: help build up down test log push deploy

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(lastword $(MAKEFILE_LIST)) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

# Common environment variables
export LOCAL_DOCKER_REPO ?= cms
export LOCAL_DOCKER_TAG ?= latest

build: ## Build Docker image.
	docker-compose -f docker-compose.build.yml \
		build --force-rm

up: ## Run Docker containers.
	docker-compose up -d

down: ## Clean Docker containers, networks, and volumes.
	docker-compose down

test: ## Test Docker image. (Need 'make up')
	docker-compose exec -T web vendor/bin/phpunit

log: ## View Docker logs.
	docker-compose logs -f

push: ## Push image to Docker repo. (via Travis-CI)
	bin/docker_push.sh ${DOCKER_USER} ${DOCKER_PASS} \
		"${LOCAL_DOCKER_REPO}:${LOCAL_DOCKER_TAG}" ridibooks/cms ${TRAVIS_TAG}

deploy: ## Trigger CI pipeline for deploying (via Travis-CI)
	bin/deploy.sh prod ${CI_TRIGGER_TOKEN} ${TRAVIS_TAG}

deploy-dev: ## Trigger CI pipeline for deploying to dev environment (via Travis-CI)
	bin/deploy.sh dev ${CI_TRIGGER_TOKEN} ${TRAVIS_BRANCH}
