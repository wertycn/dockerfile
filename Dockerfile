FROM richarvey/nginx-php-fpm:latest

RUN wget https://github.com/gongfuxiang/shopxo/archive/v2.3.3.zip -O /shopxo-2.3.3.zip
RUN unzip -d /var/www/html /shopxo-2.3.3.zip




