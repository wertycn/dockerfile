FROM centos:7
RUN yum -y install fontconfig \
    && wget https://dl.google.com/linux/direct/google-chrome-stable_current_x86_64.rpm -O /google-chrome-stable_current_x86_64.rpm \
    && yum -y install fontconfig \
    && yum -y localinstall /google-chrome-stable_current_x86_64.rpm
ADD WeiRuanYaHei.ttf /usr/share/fonts
RUN fc-cache -vf