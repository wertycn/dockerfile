FROM sonarsource/sonar-scanner-cli:4.6
USER root
RUN apk add --no-cache curl bind-tools wget busybox-extras busybox

