#!/bin/bash

# Este script faz o download da pasta de back-end do server 
# "/var/www/html/policiamento" -> "../../versions/"
# A pasta versions, mantém um histórico de versões do server, localmente.

  
# Usualmente project_root = /c/Users/42731/wamp64/www/versions/
project_root=$( dirname $PWD )
app_local_path=${project_root}/app

echo "Pasta local policiamento/app = " $app_local_path
# Remove o app atual, e cria nova pasta APP vazia, para receber o download
if [ -d "$app_local_path" ]; then
     rm -r "$app_local_path"
fi 
mkdir $app_local_path


remote_username="bitnami"
remote_server_ip="184.72.238.232"
remote_project_path=/var/www/html/policiamento/app 

echo "Pasta local de download = " $app_local_path

ssh_private_key_path="../keys/LightsailDefaultKey-us-east-1.pem"

#### Faz o download da pasta  $remote_project_path (/var/www/html/policiamento/app)
scp -i "$ssh_private_key_path" -r "$remote_username@$remote_server_ip:$remote_project_path/*" "$app_local_path"
