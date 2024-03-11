FROM pytorch/pytorch:2.2.1-cuda11.8-cudnn8-devel
RUN pip install "xinference[all]"
