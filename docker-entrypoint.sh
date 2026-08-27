#!/bin/sh
set -e

# Render define a porta em $PORT em tempo de execução; o Apache precisa escutar nela.
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

exec "$@"
