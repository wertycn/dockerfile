FROM centos:7
RUN wget https://dl.google.com/linux/direct/google-chrome-stable_current_x86_64.rpm -O /google-chrome-stable_current_x86_64.rpm
RUN yum -y install fontconfig
RUN yum -y localinstall /google-chrome-stable_current_x86_64.rpm
ADD WeiRuanYaHei.ttf /usr/share/fonts
RUN fc-cache -vf
RUN yum -y groupinstall "Development tools"
RUN yum install -y ncurses-devel gdbm-devel xz-devel sqlite-devel tk-devel uuid-devel readline-devel bzip2-devel libffi-devel
RUN yum install -y openssl-devel openssl11 openssl11-devel
RUN wget https://www.python.org/ftp/python/3.10.4/Python-3.10.4.tgz && \
    export CFLAGS=$(pkg-config --cflags openssl11) &&\ 
    export LDFLAGS=$(pkg-config --libs openssl11)
RUN tar xvzf Python-3.10.4.tgz && \
    cd Python-3.10.4 && \
    ./configure --enable-optimizations && make altinstall 
RUN ln -sf /usr/local/bin/python3.10 /usr/bin/python && \ 
    ln -sf /usr/local/bin/pip3.10  /usr/bin/pip


