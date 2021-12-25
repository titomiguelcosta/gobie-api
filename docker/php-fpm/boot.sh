#!/bin/bash

php /application/bin/console doctrine:migrations:migrate -n

/usr/sbin/php-fpm8.0
