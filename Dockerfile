FROM php:8.0-fpm

# Update packages and  Install PHP and composer dependencies
RUN apt-get update &&  apt-get install -qq git curl libzip-dev libmcrypt-dev libjpeg-dev libpng-dev libfreetype6-dev libbz2-dev

# Clear out the local repository of retrieved package files
RUN apt-get clean

# Install needed extensions
# Here you can install any other extension that you need during the test and deployment process
RUN docker-php-ext-install pdo_mysql zip bcmath

# Install Composer
RUN curl --silent --show-error "https://getcomposer.org/installer" | php -- --install-dir=/usr/local/bin --filename=composer

# Set up working directory
WORKDIR /var/www

# Copy application files
COPY ../.. .

# Set up permissions
COPY --chown=www:www . /var/www


EXPOSE 9000
