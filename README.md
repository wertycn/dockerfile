# LawGLM 复赛基础镜像

## 项目结构

确保你的项目目录结构如下：

```
.
├── Dockerfile
├── run.sh
└── tcdata
    └── question_c.json
```

## 文件说明

### Dockerfile

```Dockerfile
# 使用一个轻量级的基础镜像
FROM alpine:latest

# 设置工作目录
WORKDIR /app

# 复制run.sh到容器的/app目录
COPY run.sh /app/run.sh

# 给run.sh赋予可执行权限
RUN chmod +x /app/run.sh

# 设置容器启动时运行的命令
CMD ["/app/run.sh"]
```

### run.sh

```bash
#!/bin/sh

# 检查测试集文件是否存在
if [ -f /tcdata/question_c.json ]; then
  echo "测试集文件存在: /tcdata/question_c.json"
  
  # 读取测试集并打印
  cat /tcdata/question_c.json
  # 将测试集内容写入结果文件 替换为答题逻辑生成答案
  cat /tcdata/question_c.json > /app/result.json
else
  echo "测试集文件不存在: /tcdata/question_c.json"
fi

# 检查结果文件是否存在
if [ -f /app/result.json ]; then
  echo "结果文件已创建: /app/result.json"
else
  echo "结果文件未创建"
fi
```

## 构建 Docker 镜像

在项目根目录下，打开终端并运行以下命令来构建 Docker 镜像：

```sh
docker build -t my-docker-image .
```

该命令会读取当前目录下的 `Dockerfile` 并构建名为 `my-docker-image` 的 Docker 镜像。

## 运行 Docker 容器

构建完成后，运行以下命令启动容器：

```sh
docker run --name my-docker-container -v $(pwd)/tcdata:/tcdata my-docker-image
```

这里，`-v $(pwd)/tcdata:/tcdata` 选项将主机上的 `tcdata` 目录挂载到容器中的 `/tcdata` 目录，以确保测试集文件能够被容器访问。

## 验证结果

容器启动后，`run.sh` 脚本将执行以下操作：

1. 检查 `/tcdata/question_c.json` 文件是否存在。
2. 如果文件存在，打印其内容并将内容写入 `/app/result.json` 文件。
3. 检查结果文件 `/app/result.json` 是否成功创建，并打印相关信息。

## 常见问题

### 1. 如何查看容器的输出？

可以使用以下命令查看容器的日志输出：

```sh
docker logs my-docker-container
```

### 2. 如何进入正在运行的容器？

可以使用以下命令进入正在运行的容器：

```sh
docker exec -it my-docker-container sh
```

### 3. 如何停止并删除容器？

可以使用以下命令停止并删除容器：

```sh
docker stop my-docker-container
docker rm my-docker-container
```

### 4. 如何删除 Docker 镜像？

可以使用以下命令删除 Docker 镜像：

```sh
docker rmi my-docker-image
```
