# Build argument for environment (dev or production)
ARG ENVIRONMENT=prod

FROM php:8.3-fpm AS base

# Install packages common for all builds
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    cron \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    logrotate \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    opcache

RUN mkdir -p /var/log/app/exim4 && \
    chown www-data:www-data /var/log/app/exim4 && \
    chmod 755 /var/log/app/exim4

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Development stage with xdebug
FROM base AS dev
RUN pecl install xdebug && \
    docker-php-ext-enable xdebug

COPY ./docker/php/php-dev.ini /usr/local/etc/php/php.ini

# Production stage without xdebug
FROM base AS prod
COPY ./docker/php/php-prod.ini /usr/local/etc/php/php.ini

# Final stage - select stage based on ENVIRONMENT arg
FROM ${ENVIRONMENT} AS final

# Pass ENVIRONMENT ARG once here
ARG ENVIRONMENT
ENV APP_ENV=${ENVIRONMENT}
ENV PHP_ENV=${ENVIRONMENT}

WORKDIR /var/www/html

# Application setup
COPY ./app/composer*.json ./
RUN composer install --no-dev --optimize-autoloader
COPY ./app .

# Infrastructure configs
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/nginx/default.conf /etc/nginx/sites-available/default
COPY ./docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./docker/cron/crontab /tmp/crontab

# System setup - permissions and directories
RUN mkdir -p /var/www/html /var/log/app /var/tmp /var/cache /var/run/php && \
    chown -R www-data:www-data /var/www && \
    chown -R www-data:www-data /var/log && \
    chown -R www-data:www-data /var/tmp && \
    chown -R www-data:www-data /var/cache && \
    chown -R www-data:www-data /var/run/php && \
    chmod -R 755 /var/www && \
    chmod -R 755 /var/log && \
    chmod -R 755 /var/tmp && \
    chmod -R 755 /var/cache && \
    chmod -R 755 /var/run/php && \
    usermod -a -G adm www-data

# Enable shell for www-data
RUN usermod --shell /bin/bash www-data

# Cron setup
RUN chmod 0644 /tmp/crontab && \
    crontab -u www-data /tmp/crontab && \
    rm /tmp/crontab

# Logging setup
RUN touch /var/log/cron.log && \
    chown www-data:www-data /var/log/cron.log && \
    chmod 666 /var/log/cron.log && \
    chmod -R 755 /var/log/exim4

# PHP-FPM configuration override
RUN rm -rf /usr/local/etc/php-fpm.conf /usr/local/etc/php-fpm.d/* && \
    echo "[global]" > /usr/local/etc/php-fpm.conf && \
    echo "error_log = /dev/null" >> /usr/local/etc/php-fpm.conf && \
    echo "daemonize = no" >> /usr/local/etc/php-fpm.conf && \
    echo "" >> /usr/local/etc/php-fpm.conf && \
    echo "[www]" >> /usr/local/etc/php-fpm.conf && \
    echo "user = www-data" >> /usr/local/etc/php-fpm.conf && \
    echo "group = www-data" >> /usr/local/etc/php-fpm.conf && \
    echo "listen = 127.0.0.1:9000" >> /usr/local/etc/php-fpm.conf && \
    echo "pm = dynamic" >> /usr/local/etc/php-fpm.conf && \
    echo "pm.max_children = 5" >> /usr/local/etc/php-fpm.conf && \
    echo "pm.start_servers = 2" >> /usr/local/etc/php-fpm.conf && \
    echo "pm.min_spare_servers = 1" >> /usr/local/etc/php-fpm.conf && \
    echo "pm.max_spare_servers = 3" >> /usr/local/etc/php-fpm.conf

EXPOSE 80 9003 9001

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
