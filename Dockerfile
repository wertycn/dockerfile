FROM centos:centos7

ENV DEBIAN_FRONTEND=noninteractive \
    JAVA_HOME=/usr/lib/jvm/ 

    # yum -y install wget &&\
    # yum -y install telnet && \
    # yum -y install net-tools.x86_64 && 
RUN \
    JAVA_VERSION=11.0.11 && \
    JAVA_BUILD=9 && \
    yum -y update && \
    curl --silent --location --retry 3 \
        https://builds.openlogic.com/downloadJDK/openlogic-openjdk/${JAVA_VERSION}+${JAVA_BUILD}/openlogic-openjdk-${JAVA_VERSION}+${JAVA_BUILD}-linux-x64-el.rpm \
        -o /tmp/openjdk-${JAVA_VERSION}.rpm && \
    mkdir -p /usr/lib/jvm && mv /tmp/openjdk-"${JAVA_VERSION}" "${JAVA_HOME}" && \
    yum -y clean all && \
    rm -rf /tmp/* /var/cache/yum/* /var/tmp/* && \
    update-alternatives --install "/usr/bin/java" "java" "${JAVA_HOME}/bin/java" 1 && \
    update-alternatives --install "/usr/bin/javac" "javac" "${JAVA_HOME}/bin/javac" 1 && \
    update-alternatives --set java "${JAVA_HOME}/bin/java"