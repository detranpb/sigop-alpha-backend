#!/bin/bash

# Usualmente project_root = /c/Users/42731/wamp64/www/policiamento
project_root=$( dirname $PWD ) 
app_local_path=${project_root}/app
apiFile=${project_root}/policiamento-services.php
apiBkpFile=${project_root}/policiamento-services-bkp.php

echo "--------- Arquivos a serem enviados -------"
echo "- Pasta do App Local: " . $app_local_path
echo "- Arquivo de API serviços: " . $apiFile

remote_username=bitnami
remote_server_ip=184.72.238.232
remote_project_path=/var/www/html/policiamento/app-homolog
ssh_private_key_path=${project_root}/keys/LightsailDefaultKey-us-east-1.pem

echo "-----------------------------------------"
echo "Pasta remota: " $remote_project_path

# scp -i "$ssh_private_key_path" -r "$app_local_path"/* "$remote_username@$remote_server_ip:$remote_project_path"


# 1]]] Criar uma cópia do arquivo da API (policiamento-services), localmente antes de enviar o arquivo
 cp -n $apiFile $apiBkpFile

 # 2]]] Transfere arquivo de serviços
 scp -i "$ssh_private_key_path" -r "$apiBkpFile"  "$remote_username@$remote_server_ip:/var/www/html/policiamento/"

# 3]]]] Transfere toda pasta app
 scp -i "$ssh_private_key_path" -r "$app_local_path"/* "$remote_username@$remote_server_ip:$remote_project_path"

echo "- Deploy feito com sucesso!"