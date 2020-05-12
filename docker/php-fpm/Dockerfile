FROM php:7.4-fpm

# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive

ARG USER_ID=1000
ARG GROUP_ID=1000
ENV APP_ENV=${APP_ENV}
ENV COMPOSER_ALLOW_SUPERUSER 1

# Install selected extensions and other stuff
RUN apt-get update &&\
    apt-get -y install vim libmcrypt-dev zip unzip \
    libzip-dev libxml2-dev\
    libpq-dev supervisor wget\
    --no-install-recommends procps

RUN docker-php-ext-install pdo pdo_pgsql

# INSTALL COMPOSER:
RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
    && curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
    && php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
    && php /tmp/composer-setup.php --no-ansi --no-dev --install-dir=/usr/local/bin --filename=composer --stable \
    && ln -s /usr/local/bin/composer /usr/local/sbin/composer \
    && rm -f /tmp/composer-setup.*


# Install git
RUN apt-get update \
    && apt-get -y install git \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

WORKDIR /var/www/socialtech/

RUN if getent passwd www-data ; then  userdel -f www-data; fi && \
    if getent group www-data ; then groupdel www-data; fi && \
    groupadd -g ${GROUP_ID} www-data && \
    useradd -l -s /bin/bash -u ${USER_ID} -g www-data www-data && \
    chown --changes --silent --no-dereference --recursive  --from=33:33 ${USER_ID}:${GROUP_ID} /usr/local/bin/composer


