# Docker Hub �����Զ��������ϴ�����

## ���ܽ���

����Github Action ����ʵ�ֵľ����Զ��������ϴ����ߣ����ڱ�дDockerfile �ļ��������ύ���Զ��������ϴ���Docker Hub �ֿ� debugicu�û���

## ʹ�÷�ʽ

1. ��ȡ�ֿ⵽����
2. ����master ��֧�г��·�֧����֧��Ϊ��Ҫ����ľ�����
3. �޸�Dockerfile Ϊ�Լ���Ҫ�ľ���
4. �ύ���뵽Զ�̣��ȴ�Github Action �Զ��������ɵ��Github Action Tab�鿴���ȣ�
5. �����ɹ���ʹ�þ���

```dockerfile
debugicu/${BRANCH_NAME}:latest
```

## ע������

1. `.github/workflows/docker.yml` �ļ�����ɾ�������ļ�Ϊgithub action �����ļ�