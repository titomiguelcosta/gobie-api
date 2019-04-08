#!/bin/bash

php bin/console doctrine:migrations:migrate -n

/usr/bin/php-fpm
