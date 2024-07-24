# 使用一个轻量级的基础镜像
FROM python:3.13

# 设置工作目录
WORKDIR /app

# 复制run.sh到容器的/app目录
COPY run.sh /app/run.sh

# 给run.sh赋予可执行权限
RUN chmod +x /app/run.sh

# 设置容器启动时运行的命令
CMD ["/app/run.sh"]
