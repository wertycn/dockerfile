# Docker Hub 镜像自动构建及上传工具

[![image_auth_push](https://github.com/wertycn/dockerfile/actions/workflows/auto_push.yml/badge.svg)](https://github.com/wertycn/dockerfile/actions/workflows/auto_push.yml)[![custom_tag_push](https://github.com/wertycn/dockerfile/actions/workflows/custom_tag_push.yml/badge.svg)](https://github.com/wertycn/dockerfile/actions/workflows/custom_tag_push.yml)

## 功能介绍

基于Github Action 功能实现的镜像自动构建及上传工具，用于编写Dockerfile 文件，代码提交后，自动构建并上传到Docker Hub 仓库 debugicu用户下

## 使用方式

### 自动构建

1. 拉取仓库到本地

2. 基于master 分支切出新分支，分支名为需要编译的镜像名

3. 修改Dockerfile 为自己需要的镜像

4. 提交代码到远程，等待Github Action 自动构建（可点击Github Action Tab查看进度）

   ![](http://image.werty.cn/source_blog/freeApi/a0ab7fbf785506cf561f9401627027be.png)

5. 构建成功，使用镜像

   ![image-20220112130241171](http://image.werty.cn/source_blog/freeApi/bac9e713dcbd30db3272387bafcced2a.png)

### 手动指定tag

完成自动编译后，选择`custom_tag_push` workflow， 点击`Run workflow`下拉菜单 ,输入tag信息，点击Run按钮即可

![](http://image.werty.cn/source_blog/freeApi/019fe21b72f325d9d63874a8b0affa0c.png)

镜像名模板


```dockerfile
debugicu/${BRANCH_NAME}:latest
```

## 注意事项

1. `.github/workflows/` 目录下文件不能删除，该文件为github action 配置文件