#!/bin/bash
# ========================================
# ホスト側からdocker compose execでWordPressデータを復元する
#  - uploadsを展開
#  - DBをインポート
# ========================================

set -e

if ! docker compose ps --services --filter status=running | grep -q "^wordpress$"; then
  echo "❌ wordpressコンテナが起動していません。先に npm run docker:up を実行してください。"
  exit 1
fi

PORTAL_PATH="services/wordpress/portal"
UPLOADS_ZIP="$PORTAL_PATH/uploads.zip"
SQL_FILE="$PORTAL_PATH/wordpress.sql"

if [ ! -f "$UPLOADS_ZIP" ]; then
  echo "❌ ファイルが見つかりません: $UPLOADS_ZIP"
  exit 1
fi

if [ ! -f "$SQL_FILE" ]; then
  echo "❌ ファイルが見つかりません: $SQL_FILE"
  exit 1
fi

echo "⚠️ この処理を実行すると既存のuploadsとデータベースが上書きされます"
read -p "実行しますか？（y/N）: " answer

answer=$(echo "$answer" | tr '[:upper:]' '[:lower:]')

if [[ "$answer" != "y" ]]; then
  echo "❌ 中止しました。"
  exit 0
fi

echo "📦 uploadsを展開中..."
if docker compose exec wordpress unzip -o /var/www/html/portal/uploads.zip -d /var/www/html/wp-content/; then
  echo "✅ uploadsを展開しました"
else
  echo "❌ uploadsの展開に失敗しました"
  exit 1
fi

echo "📦 DBをインポート中..."
if docker compose exec wordpress wp db import /var/www/html/portal/wordpress.sql --allow-root --path=/var/www/html; then
  echo "✅ DBをインポートしました"
else
  echo "❌ DBインポートに失敗しました"
  exit 1
fi
