FROM centos:centos7



ENV DEBIAN_FRONTEND=noninteractive \
    JAVA_HOME=/usr/lib/jvm/ 

RUN yum -y install wget &&\
    yum -y install telnet && \
    yum -y install net-tools.x86_64 && \
	yum -y install pip && \
	yum -y install nginx 
	
RUN pip install shadowsocksr-cli 	
