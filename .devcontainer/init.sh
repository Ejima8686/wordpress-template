#!/bin/bash

# .env から読み込む（存在する場合）
if [ -f /workspaces/.env ]; then
  export $(grep THEME_NAME /workspaces/.env | xargs)
fi

# テーマディレクトリを作成（なければ）
THEME_PATH="/var/www/html/wp-content/themes/${THEME_NAME}"
mkdir -p "$THEME_PATH"

# （任意）ベースファイルをコピーしたりしてもOK
# cp -r /some/template/* "$THEME_PATH"

echo "Theme directory ensured at: $THEME_PATH"
