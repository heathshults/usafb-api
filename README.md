# National Player Database (USAFB)

This project is for the national player database API.

## Install the Application

The application relies on the developer having a few package managers installed:

### Composer (PHP)

There are a few options for installing composer, please follow the [`install guide`](https://getcomposer.org/doc/00-intro.md#downloading-the-composer-executable)

If you're on a mac, I'd recommend `brew install composer` for a global install of composer.

### Docker

Choose the install based on your operating system, please follow the [`install guide`](https://docs.docker.com/engine/installation/)

### Application Setup

To install all application dependencies:

```BASH
composer setup
```
composer update

### Start Application

To start the application on `localhost:8000`:

```BASH
composer start
```

### Running test suites

To run all test suites (phpunit and jest):

```BASH
composer test
```

### Code Coverage Metrics

To include code coverage metrics for sonar

```BASH
brew install php71-xdebug
```

### Lint and syntax check

To run react-dev-server with the todo UI:

```BASH
composer lint
```

## Start the development environment (Docker)

This application comes with a `docker-compose.yml` that will create a development environment:

```bash
docker-compose up -d
```
