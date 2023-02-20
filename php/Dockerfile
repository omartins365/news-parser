FROM php:7.4-fpm

RUN apt-get update && apt-get install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip \
    && docker-php-ext-install intl opcache pdo pdo_mysql sockets \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

# ENV RABBITMQ_URL amqp://rabbitmq_user:rabbitmq_password@rabbitmq_host:5672/%2F

WORKDIR /var/www/project

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

RUN chown -R www-data:www-data /var/www/project

# USER www-data

# Install cron package
RUN apt-get update && apt-get -y install cron
# Add the crontab file to the cron directory
COPY php/crontab /etc/cron.d/news-cron

# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/news-cron

# Apply the cron job
RUN crontab /etc/cron.d/news-cron

# Create the log file to be able to run tail
RUN touch /var/log/cron.log


# Start the cron service and tail the log file
CMD php-fpm && cron && tail -f /var/log/cron.log
