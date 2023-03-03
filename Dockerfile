FROM centos:centos7



ENV DEBIAN_FRONTEND=noninteractive \
    JAVA_HOME=/usr/lib/jvm/ 

RUN yum -y install python3 
RUN	yum -y install wget gcc pcre pcre-devel zlib zlib-devel openssl openssl-devel	
RUN pip3 install shadowsocksr-cli
RUN wget http://nginx.org/download/nginx-1.17.6.tar.gz
RUN tar -zxvf nginx-1.17.6.tar.gz 	
RUN cd nginx-1.17.6
RUN ./configure –prefix=/usr/lib/local/nginx && make && make install 
