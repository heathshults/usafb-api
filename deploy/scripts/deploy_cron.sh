#!/bin/bash

envsubst < deploy/templates/cron.yml | kubectl --namespace=$NAMESPACE_ENV create -f - || \
envsubst < deploy/templates/cron.yml | kubectl --namespace=$NAMESPACE_ENV apply -f -