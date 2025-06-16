#!/bin/bash
# ========================================
# WordPressのデータ復元
#  - uploadsを展開
#  - DBをインポート
# ========================================

PORTAL_PATH="/var/www/html/portal"
UPLOADS_ZIP="$PORTAL_PATH/uploads.zip"
SQL_GZ="$PORTAL_PATH/wordpress.sql.gz"
SQL_FILE="$PORTAL_PATH/wordpress.sql"

echo "⚠️ この処理を実行すると既存のuploadsとデータベースが上書きされます"
read -p "実行しますか？（y/N）: " answer

answer=$(echo "$answer" | tr '[:upper:]' '[:lower:]')

if [[ "$answer" != "y" ]]; then
  echo "❌ 中止しました。"
  exit 0
fi

if unzip -o "$UPLOADS_ZIP" -d /var/www/html/wp-content/; then
  echo "✅ uploadsを展開しました: $UPLOADS_ZIP"
else
  echo "❌ uploadsの展開に失敗しました"
  exit 1
fi

gunzip -f "$SQL_GZ";
if wp db import "$SQL_FILE" --allow-root --path=/var/www/html; then
  echo "✅ DBをインポートしました: $SQL_FILE"
else
  echo "❌ DBインポートに失敗しました"
  exit 1
fi
