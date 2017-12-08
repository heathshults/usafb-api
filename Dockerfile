FROM registry.gitlab.com/bluestarsports/devops/docker-base-images/bss-php71:latest

RUN apt-get update \
    && apt-get -y --no-install-recommends install php7.1-bcmath php7.1-mongo php7.1-mysql php-mysql \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

ARG GIT_DEPLOYED_SHA
ENV GIT_DEPLOYED_SHA ${GIT_DEPLOYED_SHA}

COPY . /application

COPY /docker/php-fpm/php-ini-overrides.ini /etc/php/7.1/fpm/conf.d/99-overrides.ini

EXPOSE 9000

WORKDIR "/application"

ENTRYPOINT . /application/start_application.sh