FROM nginx:latest

MAINTAINER debug.icu<debugicu@163.com>

RUN apt-get update
RUN apt -y install iputils-ping
RUN apt -y install curl
RUN apt -y install net-tools
RUN apt -y install telnet
RUN apt -y install vim



