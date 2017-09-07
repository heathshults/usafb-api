FROM registry.gitlab.com/bluestarsports/devops/docker-base-images/bss-php71:latest

ARG GIT_DEPLOY_SHA
ENV GIT_DEPLOY_SHA ${GIT_DEPLOY_SHA}

COPY . /application

COPY /docker/php-fpm/php-ini-overrides.ini /etc/php/7.1/fpm/conf.d/99-overrides.ini

EXPOSE 9000

WORKDIR "/application"

ENTRYPOINT . /application/start_application.sh
