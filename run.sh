#!/bin/sh

# 检查测试集文件是否存在
if [ -f /tcdata/question_c.json ]; then
  echo "测试集文件存在: /tcdata/question_c.json"
  
  # 读取测试集并打印
  cat /tcdata/question_c.json

  # 将测试集内容写入结果文件
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
