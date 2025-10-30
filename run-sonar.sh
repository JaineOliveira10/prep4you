#!/bin/bash

echo "Iniciando SonarQube..."
docker-compose -f docker-compose.sonar.yml up -d

echo "Aguardando SonarQube inicializar..."
echo "Verificando se SonarQube está rodando..."
until curl -s http://localhost:9000/api/system/status | grep -q '"status":"UP"'; do
  echo "Aguardando SonarQube..."
  sleep 10
done
echo "SonarQube está pronto!"

echo "Executando análise do SonarQube..."
docker run --rm \
  --network host \
  -v "$(pwd):/usr/src" \
  sonarsource/sonar-scanner-cli \
  -Dsonar.host.url=http://localhost:9000 \
  -Dsonar.login=admin \
  -Dsonar.password=admin

echo "Análise concluída! Acesse http://localhost:9000"