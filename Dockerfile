FROM sonarsource/sonar-scanner-cli:4.6
USER root
RUN apk add curl  \
    && apk add --no-cache npm \
    && apk add --no-cache nodejs

