docker-purge-all: docker-purge-containers docker-purge-images

docker-purge-containers:
	docker rm --force $(shell docker ps -qa)

docker-purge-images:
	docker rmi --force $(shell docker images -qa)

docker-run:
	docker-compose -f docker/docker-compose.local.yml up

php-fix:
	php vendor/bin/php-cs-fixer fix src/

deploy:
	php bin/dep deploy prod
