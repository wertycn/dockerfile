FROM centos:centos7



ENV DEBIAN_FRONTEND=noninteractive \
    JAVA_HOME=/usr/lib/jvm/ 

RUN yum -y install python3 
RUN	yum -y install nginx 	
RUN pip3 install shadowsocksr-cli 	
