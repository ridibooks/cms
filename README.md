# CMS

[![Build Status](https://travis-ci.org/ridi/cms.svg?branch=2.x)](https://travis-ci.org/ridi/cms)
[![](https://images.microbadger.com/badges/image/ridibooks/cms.svg)](https://microbadger.com/images/ridibooks/cms "Get your own image badge on microbadger.com")
[![](https://images.microbadger.com/badges/version/ridibooks/cms.svg)](https://microbadger.com/images/ridibooks/cms "Get your own version badge on microbadger.com")


## Overview
This is a main server of RIDI CMS service.

## Getting started
```
git clone https://github.com/ridi/cms.git
cd cms
make all phinx env-dev
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
