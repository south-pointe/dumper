FROM php:8-cli-alpine

ENV PATH="${PATH}:/app/vendor/bin"
ENV TERM xterm-256color
ENV TZ Asia/Tokyo

COPY docker/files /

RUN set -e \
    && apk add --no-cache bash coreutils git \
    && apk add --no-cache --virtual=.build-deps autoconf g++ make \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && apk del .build-deps \
    && docker-php-source delete \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer \
    && mkdir -p /app

WORKDIR /app
