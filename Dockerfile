FROM php:7.0-apache

RUN apt-get update -y && \
    apt-get install -y \
    libmagick++-dev \
    libmagickwand-dev \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    libmcrypt-dev \
    # libpng12-dev \
    libjpeg-dev \
    && apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*


RUN docker-php-ext-configure gd \
    --enable-gd-native-ttf \
    --with-freetype-dir=/usr/include/freetype2 \
    --with-png-dir=/usr/include \
    --with-jpeg-dir=/usr/include

# RUN docker-php-ext-configure gd --with-jpeg --with-freetype

# RUN docker-php-ext-install mysqli mbstring zip gd
RUN docker-php-ext-install mysqli gd

RUN a2enmod rewrite
RUN service apache2 restart

# Permissions
RUN chown -R root:www-data /var/www/html
RUN chmod u+rwx,g+rx,o+rx /var/www/html