FROM centos:centos7



ENV DEBIAN_FRONTEND=noninteractive \
    JAVA_HOME=/usr/lib/jvm/ 

RUN yum -y install pip 
RUN	yum -y install nginx 	
RUN pip install shadowsocksr-cli 	
