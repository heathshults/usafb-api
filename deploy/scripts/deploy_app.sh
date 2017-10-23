#!/bin/bash

sed -ie "s/THIS_STRING_IS_REPLACED_DURING_BUILD/$(date)/g" deploy/templates/deployment.yml
kubectl create -f deploy/templates/deployment.yml --namespace=$NAMESPACE_ENV --validate=false || kubectl replace -f deploy/templates/deployment.yml --namespace=$NAMESPACE_ENV
