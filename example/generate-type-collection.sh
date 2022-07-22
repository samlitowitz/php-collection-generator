#!/usr/bin/env bash

DOCKER_USER=${DOCKER_USER-$(id -u):$(id -g)}
SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )

cd $SCRIPT_DIR/../

docker-compose run php /app/bin/php-collection-generator --config /app/example/php-collection-generator.json generate /app/src/App/Console/Config
