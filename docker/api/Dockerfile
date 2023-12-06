FROM php:8.1-apache-buster

RUN apt update

ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf
RUN sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/bin/composer
RUN chmod a+x /usr/bin/composer

RUN apt install -y git mariadb-client libonig-dev
RUN apt install -y --allow-downgrades libzip-dev 
RUN docker-php-ext-install mbstring pdo pdo_mysql zip mysqli
RUN apt install -y libxml2-dev
RUN docker-php-ext-install intl

# enable the rewrite engine
RUN a2enmod rewrite
RUN a2enmod headers

# make sure the doc root allows .htaccess overrides
RUN sed -ri -e "/DocumentRoot/a \
    #allow override settings\r\n\
    <Directory $APACHE_DOCUMENT_ROOT>\r\n\
        DirectoryIndex index.php\r\n\
        Options Indexes FollowSymLinks\r\n\
        AllowOverride All\r\n\
        Require all granted\r\n\
    </Directory>\r\n" /etc/apache2/sites-available/*.conf

USER root
RUN apt update
RUN apt install -y libpng-dev
RUN apt-get install -y \
    libwebp-dev \
    libjpeg62-turbo-dev \
    libpng-dev libxpm-dev \
    libfreetype6-dev

RUN docker-php-ext-configure gd \
    --with-jpeg \
    --with-freetype
RUN docker-php-ext-install gd

RUN echo 'memory_limit = 512M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini;
RUN echo 'upload_max_filesize = 128M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini;
RUN echo 'post_max_size = 128M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini;

RUN docker-php-ext-install exif

