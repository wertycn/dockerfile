# Docker Hub �����Զ��������ϴ�����

## ���ܽ���

����Github Action ����ʵ�ֵľ����Զ��������ϴ����ߣ����ڱ�дDockerfile �ļ��������ύ���Զ��������ϴ���Docker Hub �ֿ� debugicu�û���

## ʹ�÷�ʽ

### �Զ�����

1. ��ȡ�ֿ⵽����
2. ����master ��֧�г��·�֧����֧��Ϊ��Ҫ����ľ�����
3. �޸�Dockerfile Ϊ�Լ���Ҫ�ľ���
4. �ύ���뵽Զ�̣��ȴ�Github Action �Զ��������ɵ��Github Action Tab�鿴���ȣ�
5. �����ɹ���ʹ�þ���

### �ֶ�ָ��tag

![](http://image.werty.cn/source_blog/freeApi/019fe21b72f325d9d63874a8b0affa0c.png)

������ģ��


```dockerfile
debugicu/${BRANCH_NAME}:latest
```

## ע������

1. `.github/workflows/` Ŀ¼���ļ�����ɾ�������ļ�Ϊgithub action �����ļ�