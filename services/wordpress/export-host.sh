#!/bin/bash
# ========================================
# ホスト側からdocker compose execでWordPressデータをエクスポートする
#  - データベースをエクスポート（.sql）
#  - uploadsをzip圧縮
# ========================================

set -e

if ! docker compose ps --services --filter status=running | grep -q "^wordpress$"; then
  echo "❌ wordpressコンテナが起動していません。先に npm run docker:up を実行してください。"
  exit 1
fi

echo "📦 DBをエクスポート中..."
if docker compose exec wordpress wp db export /var/www/html/portal/wordpress.sql --allow-root --path=/var/www/html; then
  echo "✅ DBをエクスポートしました: services/wordpress/portal/wordpress.sql"
else
  echo "❌ DBエクスポート失敗"
  exit 1
fi

echo "📦 uploadsをエクスポート中..."
if docker compose exec wordpress sh -c 'cd /var/www/html/wp-content && zip -r /var/www/html/portal/uploads.zip uploads'; then
  echo "✅ uploadsをエクスポートしました: services/wordpress/portal/uploads.zip"
else
  echo "❌ uploadsエクスポート失敗"
  exit 1
fi
