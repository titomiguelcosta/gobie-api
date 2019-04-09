#!/bin/bash

php /application/bin/console doctrine:migrations:migrate -n

/usr/bin/php-fpm
