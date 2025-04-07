FROM wordpress:php8.2-apache

RUN apt-get update \
    && apt-get -y install vim wget zip unzip less default-mysql-client \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer --version

COPY entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh
