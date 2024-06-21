FROM php:7.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libvips-tools

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Arguments defined in docker-compose.yml
#ARG user
#ARG uid

# Create system user to run Composer and Artisan Commands
#RUN useradd -G www-data,root -u $uid -d /home/$user $user
#RUN mkdir -p /home/$user/.composer && \
#    chown -R $user:$user /home/$user

RUN useradd -G www-data,root -u 1004 -d /home/iviewer iviewer
RUN mkdir -p /home/iviewer/.composer && \
    chown -R iviewer:iviewer /home/iviewer

# Set working directory
WORKDIR /var/www

#RUN composer install # for dev
#RUN composer install --no-dev # for prod

#USER $user
USER iviewer 

#CMD ["sh", "-c", ""php artisan key:generate && php artisan migrate:refresh --seed"]           
