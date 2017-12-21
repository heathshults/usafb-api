export env=$NAMESPACE_ENV
export NR_INSTALL_KEY=$NR_APP_KEY 
envsubst < docker/newrelic/newrelic-example.ini > docker/newrelic/newrelic.ini

