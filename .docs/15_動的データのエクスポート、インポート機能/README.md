# 動的データのエクスポート、インポート機能

---

## 目次

- [構造](#構造)
  - [portalディレクトリ](#portalディレクトリ)
  - [エクスポート](#エクスポート)
  - [インポート](#インポート)

---

> 本開発環境では、テーマ開発中に作成した投稿やタクソノミー、アップロードした画像などを**エクスポートし、表側で管理、他エンジニアと共有することが可能**です。　また、**そのデータをインポートすることもできます。**
> コマンドを実行することで、`wordpress.sql` 、`uploads.zip` がエクスポートされます。

## 構造

```markdown
services/
└── wordpress/
├── portal/
│ ├── wordpress.sql ... データベース
│ └── uploads.zip ...　アップロードされた画像等
├── export.sh
└── import.sh
```

### portalディレクトリ

出力データを格納するディレクトリです。

インポートの際もここを参照します。

`docker-compose.yml`にて`/var/www/html/portal` にマウントしています。

```markdown
    volumes:

        ~ 省略 ~

      - "../services/wordpress/portal:/var/www/html/portal"
```

### エクスポート

[WP-CLI](https://wp-cli.org/ja/)を利用して、データをエクスポートしています。

```shell
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
```

- `/var/www/html/portal` に`wordpress.sql` 、`uploads.zip`を出力します。画像データは重くなりがちなので、zipファイルで出力しています。

### インポート

```shell
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
```

- インポートを実行する前に、確認メッセージをだします。`y`もしくは`Y`を入力することで進行します。
- `/var/www/html/portal` に`wordpress.sql` 、`uploads.zip` があれば、それぞれのインポートを実行します。
