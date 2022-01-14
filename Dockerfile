FROM alpine:latest
RUN apk add --no-cache npm \
&& apk add --no-cache nodejs
