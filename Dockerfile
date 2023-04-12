FROM richarvey/nginx-php-fpm:1.6.0

RUN wget https://github.com/gongfuxiang/shopxo/archive/v2.3.3.zip -O /shopxo.zip \
  && unzip -d /var/www/html /shopxo.zip  \
  && mv /var/www/html/shopxo-2.3.3/ /var/www/html/shopxo/ \
  && mkdir -p /usr/local/nginx/logs \
  && rm -rf /etc/nginx/sites-enabled/* 
COPY shopxo.conf /etc/nginx/sites-enabled/shopxo.conf




