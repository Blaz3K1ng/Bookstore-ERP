#!/bin/sh
# Substitute only ${API_GATEWAY_URL}, leaving all nginx $variables untouched.
# Defaults to the Docker Compose service name for local development.
: "${API_GATEWAY_URL:=http://api-gateway:8000}"
envsubst '${API_GATEWAY_URL}' \
    < /etc/nginx/nginx.conf.template \
    > /etc/nginx/conf.d/default.conf
