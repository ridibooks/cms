# CMS

[![Build Status](https://travis-ci.org/ridi/cms.svg?branch=2.x)](https://travis-ci.org/ridi/cms)
[![](https://images.microbadger.com/badges/image/ridibooks/cms.svg)](https://microbadger.com/images/ridibooks/cms "Get your own image badge on microbadger.com")
[![](https://images.microbadger.com/badges/version/ridibooks/cms.svg)](https://microbadger.com/images/ridibooks/cms "Get your own version badge on microbadger.com")


## Overview
This is a main server of RIDI CMS service.

## Getting Started
```bash
git clone https://github.com/ridi/cms.git
cd cms

make build      # Build Docker image
make up         # Run services

sleep 30s       # (Wait for DB creating..)
make db         # Initialize DB schema

sleep 3s        # (Wait for DB schema changing..)
make test       # Run test

open http://localhost

make log        # Watch docker-compose logs
make down       # Clean Docker resources
```

## Build
You can get the following images as a result of the `make build`. See docker-compose.build.yml

- cms
- cms-builder

## Manage DB schema
We use [Phinx](https://phinx.org) to manage DB schema.
```bash
# Create new DB migration.
vendor/bin/phinx create NewMigrationName

# Edit the skeleton file created in db/migrations
vim db/migrations/20180123123456_new_migration_name.php

# Apply the migration.
vendor/bin/phinx migrate
```

## Deployment
We use Travis CI to deploy. See [.travis.yml](./.travis.yml)  
You needs to be careful when push tags or create a release. 
