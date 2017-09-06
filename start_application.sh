#!/bin/bash

echo "::> starting fpm"
/usr/sbin/php-fpm7.1 -F
echo "fpm shutdown"
