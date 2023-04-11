FROM richarvey/nginx-php-fpm:latest

RUN wget https://github.com/gongfuxiang/shopxo/archive/v2.3.3.zip -O /shopxo.zip \
  && unzip -d /var/www/html /shopxo.zip  \
  && rm -rf /etc/nginx/sites-enabled/*
COPY shopxo.conf /etc/nginx/sites-enabled/shopxo.conf




