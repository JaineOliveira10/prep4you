#!/bin/bash

if [ -z "$1" ]; then
    echo "Uso: ./analyze.sh <SEU_TOKEN>"
    echo "Gere um token em: http://localhost:9000 > User > My Account > Security"
    exit 1
fi

docker run --rm \
  --network host \
  -v "$(pwd):/usr/src" \
  sonarsource/sonar-scanner-cli \
  -Dsonar.host.url=http://localhost:9000 \
  -Dsonar.token=$1