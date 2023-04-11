FROM php:7.3.32-fpm-alpine3.13
LABEL MAINTAINER="debugicu@163.com"
ENV TZ "Asia/Shanghai"

# 时区
RUN echo ${TZ} >/etc/timezone

# 创建www用户
RUN addgroup -g 1000 -S www && adduser -s /sbin/nologin -S -D -u 1000 -G www www

RUN echo $PHPIZE_DEPS

# PHPIZE_DEPS包含gcc g++等编译辅助类库，完成后删除;pecl安装扩展。
RUN apk add --no-cache $PHPIZE_DEPS \
    && apk add --no-cache libstdc++ libzip-dev vim\
    && apk update \
    && pecl install redis-5.3.0 \
    && pecl install zip \
    && pecl install swoole \
    && docker-php-ext-enable redis zip swoole\
    && apk del $PHPIZE_DEPS

# docker-php-ext-install安装扩展。
RUN apk update \
    && apk add --no-cache nginx freetype libpng libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev  \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install -j$(nproc) pdo_mysql opcache bcmath mysqli

# 安装Composer
RUN php -r "copy('https://install.phpcomposer.com/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

# Nginx配置
COPY default.conf /etc/nginx/http.d/
COPY index.php /var/www/html
# 在run.sh
COPY run.sh /run.sh
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && mkdir -p /run/nginx/ && chmod +x /run.sh
# 暴露端口
EXPOSE 80
# 执行run.sh
ENTRYPOINT ["/run.sh"]
