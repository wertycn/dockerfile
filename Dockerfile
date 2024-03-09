FROM registry.cn-hangzhou.aliyuncs.com/modelscope-repo/modelscope:ubuntu22.04-cuda11.8.0-py310-torch2.1.0-tf2.14.0-1.10.0

RUN pip install "xinference[all]"
