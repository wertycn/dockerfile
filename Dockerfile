FROM centos:centos7

ENV DEBIAN_FRONTEND=noninteractive \
    JAVA_HOME=/usr/lib/jvm/ 

    # yum -y install wget &&\
    # yum -y install telnet && \
    # yum -y install net-tools.x86_64 && 
RUN \
    JAVA_VERSION=11.0.11 && \
    JAVA_BUILD=9 && \
    curl --silent --location --retry 3 \
        https://builds.openlogic.com/downloadJDK/openlogic-openjdk/${JAVA_VERSION}+${JAVA_BUILD}/openlogic-openjdk-${JAVA_VERSION}+${JAVA_BUILD}-linux-x64-el.rpm \
        -o /tmp/openjdk-${JAVA_VERSION}.rpm 
RUN rpm -ivh /tmp/openjdk-${JAVA_VERSION}.rpm