# Docker Hub �����Զ��������ϴ�����

[![image_auth_push](https://github.com/wertycn/dockerfile/actions/workflows/auto_push.yml/badge.svg)](https://github.com/wertycn/dockerfile/actions/workflows/auto_push.yml)[![custom_tag_push](https://github.com/wertycn/dockerfile/actions/workflows/custom_tag_push.yml/badge.svg)](https://github.com/wertycn/dockerfile/actions/workflows/custom_tag_push.yml)

## ���ܽ���

����Github Action ����ʵ�ֵľ����Զ��������ϴ����ߣ����ڱ�дDockerfile �ļ��������ύ���Զ��������ϴ���Docker Hub �ֿ� debugicu�û���

## ʹ�÷�ʽ

### �Զ�����

1. ��ȡ�ֿ⵽����

2. ����master ��֧�г��·�֧����֧��Ϊ��Ҫ����ľ�����

3. �޸�Dockerfile Ϊ�Լ���Ҫ�ľ���

4. �ύ���뵽Զ�̣��ȴ�Github Action �Զ��������ɵ��Github Action Tab�鿴���ȣ�

   ![](http://image.werty.cn/source_blog/freeApi/a0ab7fbf785506cf561f9401627027be.png)

5. �����ɹ���ʹ�þ���

   ![image-20220112130241171](http://image.werty.cn/source_blog/freeApi/bac9e713dcbd30db3272387bafcced2a.png)

### �ֶ�ָ��tag

����Զ������ѡ��`custom_tag_push` workflow�� ���`Run workflow`�����˵� ,����tag��Ϣ�����Run��ť����

![](http://image.werty.cn/source_blog/freeApi/019fe21b72f325d9d63874a8b0affa0c.png)

������ģ��


```dockerfile
debugicu/${BRANCH_NAME}:latest
```

## ע������

1. `.github/workflows/` Ŀ¼���ļ�����ɾ�������ļ�Ϊgithub action �����ļ�