FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && a2enmod rewrite headers \
    && printf '<Directory /var/www/html>\n  AllowOverride All\n  Require all granted\n</Directory>\n' >> /etc/apache2/apache2.conf \
    && rm -rf /var/lib/apt/lists/*

# Poucos processos: cada um mantém uma conexão persistente com o Supabase (app/bootstrap.php),
# então isto limita o total de conexões abertas. 10 é folgado para o tráfego do site e cabe
# na memória do plano gratuito do Render.
RUN printf '<IfModule mpm_prefork_module>\n  StartServers 2\n  MinSpareServers 2\n  MaxSpareServers 5\n  MaxRequestWorkers 10\n  MaxConnectionsPerChild 1000\n</IfModule>\n' > /etc/apache2/mods-available/mpm_prefork.conf

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

COPY . /var/www/html/
# Arquivos de infraestrutura não devem ficar acessíveis pela web.
RUN rm -f /var/www/html/Dockerfile /var/www/html/docker-entrypoint.sh \
    /var/www/html/vercel.json /var/www/html/.dockerignore /var/www/html/.gitignore

ENV PORT=8080
EXPOSE 8080

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
