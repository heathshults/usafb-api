FROM registry.gitlab.com/bluestarsports/devops/docker-base-images/bss-php71:latest

ARG GIT_DEPLOYED_SHA ""
ENV GIT_DEPLOYED_SHA ${GIT_DEPLOYED_SHA}

COPY . /application

COPY /docker/php-fpm/php-ini-overrides.ini /etc/php/7.1/fpm/conf.d/99-overrides.ini

EXPOSE 9000

WORKDIR "/application"

ENTRYPOINT . /application/start_application.sh
