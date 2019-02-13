FROM ubuntu:18.04

WORKDIR /project/integration-source

# Workaround for tzdata asking for input
# https://askubuntu.com/questions/909277/avoiding-user-interaction-with-tzdata-when-installing-certbot-in-a-docker-contai
ENV DEBIAN_FRONTEND=noninteractive

# Prepare PHP installation
RUN apt-get update && apt-get install --yes composer unzip php7.2-cli php7.2-mbstring php7.2-dom php7.2-curl

# Install basic Yii application from upstream
RUN composer create-project --no-interaction --prefer-dist yiisoft/yii2-app-basic .

# Make project files available for testing
COPY . /project/library

# Pretend that our library is installable by Composer
RUN composer config --no-interaction repositories.lucidtaz path /project/library

# Install our library into the Yii application
RUN composer require --no-interaction "lucidtaz/yii2-scssphp @dev"

# Put our integration test application files in the right spot
RUN mkdir -p assets/source
RUN cp /project/library/docker/integration/files/AppAsset.php assets/AppAsset.php
RUN cp /project/library/docker/integration/files/test.scss assets/source/test.scss
RUN cp /project/library/docker/integration/files/config_web.php config/web.php

EXPOSE 8080

CMD php -S 0.0.0.0:8080 -t web