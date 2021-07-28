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

phpunit:
	php vendor/bin/phpunit

debug:
	XDEBUG_CONFIG="idekey=PHPSTORM" PHP_IDE_CONFIG="serverName=127.0.0.1" php -dxdebug.remote_enable=1 -dxdebug.remote_autostart=1 -dxdebug.remote_host=127.0.0.1 -S localhost:9100 -t public/


