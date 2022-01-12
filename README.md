# Docker Hub 镜像自动构建及上传工具

## 功能介绍

基于Github Action 功能实现的镜像自动构建及上传工具，用于编写Dockerfile 文件，代码提交后，自动构建并上传到Docker Hub 仓库 debugicu用户下

## 使用方式

1. 拉取仓库到本地
2. 基于master 分支切出新分支，分支名为需要编译的镜像名
3. 修改Dockerfile 为自己需要的镜像
4. 提交代码到远程，等待Github Action 自动构建（可点击Github Action Tab查看进度）
5. 构建成功，使用镜像

```dockerfile
debugicu/${BRANCH_NAME}:latest
```

## 注意事项

1. `.github/workflows/docker.yml` 文件不能删除，该文件为github action 配置文件