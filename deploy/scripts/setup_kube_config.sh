#!/bin/bash

# Set Environment
kubectl config set-cluster $k8s_cluster_name --server=$k8s_api_server --insecure-skip-tls-verify=true

kubectl config set-credentials deploy_bot --token=$gitlab_service_account_token_staging

kubectl config set-context $k8s_context_name --cluster=$k8s_cluster_name --user=deploy_bot

kubectl config use-context $k8s_context_name