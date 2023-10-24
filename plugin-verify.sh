#!/usr/bin/env bash
declare -r CURRENT_DIR=$PWD
echo "彩プラグイン検証ツール" 
echo "----------------------" 
echo "検証するプラグインの情報を入力してください。"
echo " "
read -p 'プラグイン名：' pluginname
read -p '認証キー：' key

if grep -Fq "ECCUBE_PLUGIN_VERIFY_NAME =" codeception/acceptance/config.ini
then
    sed -i '/ECCUBE_PLUGIN_VERIFY_NAME =/d' codeception/acceptance/config.ini
    echo "ECCUBE_PLUGIN_VERIFY_NAME = \"$pluginname\"" >> codeception/acceptance/config.ini
else
    echo "ECCUBE_PLUGIN_VERIFY_NAME = \"$pluginname\"" >> codeception/acceptance/config.ini
fi

if grep -Fq "ECCUBE_PLUGIN_VERIFY_KEY =" codeception/acceptance/config.ini
then
    sed -i '/ECCUBE_PLUGIN_VERIFY_KEY =/d' codeception/acceptance/config.ini
    echo "ECCUBE_PLUGIN_VERIFY_KEY = $key" >> codeception/acceptance/config.ini
else
    echo "ECCUBE_PLUGIN_VERIFY_KEY = $key" >> codeception/acceptance/config.ini
fi

echo "dockerコンテナを立ち上げます。少々お待ちください。" 
docker compose -f docker-compose.yml -f docker-compose.mysql.yml -f docker-compose.codeception.yml up -d --build