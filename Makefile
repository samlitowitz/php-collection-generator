DOCKER_SERVICE=php

check: composer.json
	docker-compose run --rm --entrypoint=composer ${DOCKER_SERVICE} run check
