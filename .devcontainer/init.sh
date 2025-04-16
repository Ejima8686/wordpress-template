#!/bin/bash

# ========================================
# wp-config.phpの初期化を行います。
#
# - wp-config.php の自動生成
# - WordPress のインストールと初期設定（パーマリンク、日本語化など）
# - よく使うプラグインのインストールと有効化
# ========================================

root_path="/var/www/html"
theme_path="$root_path/wp-content/themes/$WORDPRESS_THEME_NAME"
host="$WORDPRESS_DB_HOST"
port="$WORDPRESS_DB_PORT"

docker-entrypoint.sh apache2-foreground &

if [ ! -e "$root_path/index.php" ]; then
    echo "WordPress files not found. Installing WordPress..."

	echo "Waiting for mysql"
	until (echo >/dev/tcp/$host/$port) &>/dev/null
	do
		>&2 echo -n "."
		sleep 1
	done
	>&2 echo "MySQL is up - executing command"

	cd $root_path

	wp core download https://wordpress.org/latest.zip --force --allow-root
	wp config create --dbname=wordpress --dbuser=wordpress --dbpass=wordpress --allow-root
	wp core install --url=http://localhost:8080 --title="WordPress Site" --admin_user=admin --admin_password=password --admin_email=wordpress@example.com --path="$root_path" --allow-root

	wp option update permalink_structure "/%postname%/" --allow-root
	wp option update timezone_string "Asia/Tokyo" --allow-root

	wp language core install ja --allow-root
	wp site switch-language ja --allow-root

	wp plugin install intuitive-custom-post-order --activate --allow-root
	wp plugin install wordpress-seo --activate --allow-root
	wp plugin install wp-multibyte-patch --activate --allow-root
fi

cd $root_path
composer config --no-plugins allow-plugins.composer/installers true
composer install
wp plugin activate advanced-custom-fields-pro --allow-root

cd $theme_path
composer install --no-plugins --no-scripts &
touch my-errors.log

cd $root_path
wp theme activate "$WORDPRESS_THEME_NAME" --allow-root

chown www-data:www-data -R /var/www/html/wp-content

wait
