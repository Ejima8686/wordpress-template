#!/bin/bash
# ========================================
# WordPressのデータエクスポートし、圧縮形式で保存します。
#  - データベースをエクスポートしてgzip圧縮
#  - uploadsをzip 圧縮
# ========================================

PORTAL_PATH="/var/www/html/portal"
SQL_PATH="$PORTAL_PATH/wordpress.sql"
UPLOADS_PATH="$PORTAL_PATH/uploads.zip"

if wp db export "$SQL_PATH" --allow-root --path=/var/www/html; then
  echo "✅ DBをエクスポート: $SQL_PATH"
else
  echo "❌ DBエクスポート失敗"
  exit 1
fi
 
UPLOADS_SRC="/var/www/html/wp-content/uploads"
if [ -d "$UPLOADS_SRC" ]; then
  cd /var/www/html/wp-content || exit 1
  if zip -r "$UPLOADS_PATH" uploads > /dev/null; then
    echo "✅ uploadsをエクスポート: $UPLOADS_PATH"
  else
    echo "❌ uploadsエクスポート失敗"
    exit 1
  fi
else
  echo "❌ uploadsが存在しません: $UPLOADS_SRC"
  exit 1
fi
