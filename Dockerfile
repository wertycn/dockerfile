FROM centos:7

RUN yum install pip
RUN pip install shadowsocksr-cli
RUN yum install nginx 


