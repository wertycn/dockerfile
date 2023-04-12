FROM richarvey/nginx-php-fpm:1.9.1
COPY shopxo-2.3.3/ /var/www/html/shopxo/
RUN mkdir -p /usr/local/nginx/logs \
  && rm -rf /etc/nginx/sites-enabled/* 
COPY shopxo.conf /etc/nginx/sites-enabled/shopxo.conf




