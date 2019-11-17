#!/bin/bash

php /application/bin/console doctrine:migrations:migrate -n

/usr/sbin/php-fpm7.3
