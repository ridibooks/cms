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

make build       # Build Docker image first
make up          # Run services (See docker-compose.yml)

make init-db     # Initialize schema of DB container (First time only)
make test        # Run Test
make log         # Watch docker-compose logs

make down        # Clean Docker resources
```

## Build
You can get the following images as a result of the `make build`. See docker-compose.build.yml

- cms
- cms-builder

## Manage DB schema
We use Phinx to manage DB schema.
```bash
# Set initial DB.
bin/setup.sh

# Create new DB migration.
composer phinx-create -- NewMigrationName

# Edit the skeleton file created in db/migrations
vim db/migrations/20180123123456_new_migration_name.php

# Apply the migration.
composer phinx-migrate
```

This assumes that you are running MySQL DB at localhost. (user=root, password='')  
If you want to use another, write that endpoint on .env
```
MYSQL_HOST=yourhost
MYSQL_USER=yourid
MYSQL_PASSWORD=yourpassword
MYSQL_DATABASE=yourdb
```

## Deployment
We use Travis CI to deploy. See [.travis.yml](./.travis.yml)  
You needs to be careful when push tags or create a release. 
