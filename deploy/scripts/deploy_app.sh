#!/bin/bash

# Deploy App with specific Image via Pipeline ID
envsubst < deploy/templates/deployment.yml | kubectl --namespace=$NAMESPACE_ENV create -f - || \
kubectl --namespace=$NAMESPACE_ENV set image deployment/$APP_NAME $app_container_name=$CI_REGISTRY_IMAGE:$image_tag &&
kubectl --namespace=$NAMESPACE_ENV set image deployment/$APP_NAME $app_second_container_name=$CI_REGISTRY_IMAGE/$app_second_name:$image_tag