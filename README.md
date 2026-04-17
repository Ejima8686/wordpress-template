# WordPress Template

**Docker Compose を使った WordPress 開発環境テンプレート**

## 目次

- [必要なソフト・拡張機能](#必要なソフト・拡張機能)
- [構成](#構成)
- [導入](#導入)
- [データの復元・エクスポート](#データの復元・エクスポート)
- [開発コマンド](#開発コマンド)
- [ブロックの作成](#ブロックの作成)
- [コミットテンプレート](#コミットテンプレート)

## 必要なソフト・拡張機能

以下をローカルにインストールしてください：

- [Docker Desktop](https://www.docker.com/products/docker-desktop)
- [Node.js（v18 以上推奨）](https://nodejs.org/)

## 構成

| 項目               | 内容                               |
| ------------------ | ---------------------------------- |
| **CMS**            | WordPress最新版 + PHP 8.2 + Apache |
| **DB**             | MariaDB                            |
| **開発環境**       | Docker + Docker Compose            |
| **フロントエンド** | Vite + Tailwind CSS + Alpine.js    |
| **開発ツール**     | WP-CLI、Composer、Node.js v20      |

## 導入

### 1. 初期化スクリプトの実行

```bash
npm run setup:init
```

`setup/init.mjs` が対話形式で以下を行います：

- テーマ名の確認（またはリネーム）
- `.env` の作成（THEME_NAME, VITE_THEME_NAME）
- `auth.json` の作成（ACF PRO用ファイル）

### 2. コンテナの起動

プロジェクトルートで以下を実行します。

```bash
docker compose -f docker/docker-compose.yml up
```

`docker/init.sh` に基づいて、WordPress環境が構築されます。開発を再開する場合もこちらを実行してください。

### 3. Vite の起動

コンテナ内で、Viteを起動させます。

```bash
npm run i
npm run dev
```

[http://localhost:8080/](http://localhost:8080/) にアクセスしてください

### 4. 開発スタート

ブラウザから WordPressの管理画面にアクセスし、開発を開始してください。

**管理画面URL：** [http://localhost:8080/wp-admin](http://localhost:8080/wp-admin)

**ログイン情報：**

- ユーザー名: `admin`
- パスワード: `password`

## データの復元・エクスポート

### データの復元

すでに他開発者がエクスポートしたデータがある場合、データの復元ができます。

**事前準備：**
`services/wordpress/portal` に以下があることを確認してください：

- `uploads.zip`（アップロードした画像等のファイル）
- `wordpress.sql`（投稿された記事などのデータ）

**実行コマンド：**

```bash
npm run import
```

### データのエクスポート

WordPressのデータをエクスポートできます。

**出力されるデータ：**

- `uploads.zip`（アップロードした画像等のファイル）
- `wordpress.sql`（投稿された記事などのデータ）

**実行コマンド：**

```bash
npm run export
```

## 開発コマンド

### 本番用ビルド

ビルド済みのファイルが `[テーマ名]/build` ディレクトリに出力されます。

```bash
npm run build
```

### フォーマット

コードのフォーマットを実行します。

```bash
npm run format
```

## ブロックの作成

### ACFブロックの作成

```bash
npx scaffdog generate acf-block
```

ACF PRO用のカスタムブロックを作成できます。対話に沿って生成してください。

**入力項目：**

| 項目            | 説明                                                                                                                                                                                     |
| --------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **name**        | ブロックの slug を入力                                                                                                                                                                   |
| **title**       | エディタ側で表示する名前（日本語可）                                                                                                                                                     |
| **description** | エディタ側で表示する詳細文                                                                                                                                                               |
| **icon**        | エディタ側で表示するアイコン<br>[Dashicons](https://developer.wordpress.org/resource/dashicons/)から選択<br>`dashicons-` プレフィックスは除外（例：`dashicons-menu-alt3` → `menu-alt3`） |
| **category**    | エディタ側で表示するカテゴリ<br>選択肢：`text`, `media`, `design`, `widgets`, `theme`, `embed`<br>デフォルト：`text`                                                                     |

### 作成したブロックの登録

1. `mytheme/inc/blocks.php` の `allowed_block_types_all` で、使用するブロックを配列に追加
   - 使用できるブロックは[こちら](https://wphelpers.dev/blocks)から確認
2. ACFブロックを追加する場合は、nameに入力した値に `acf/` プレフィックスを付ける
   - 例：`heading` → `acf/heading`

## コミットテンプレート

コミットメッセージの一貫性を保つため、コミットメッセージのテンプレートを用意しています。

### セットアップ

プロジェクトルートで以下のコマンドを実行し、`.github/.gitmessage.txt` をコミットの初期表示に設定：

```bash
git config commit.template .github/.gitmessage.txt
```

### 使用方法

`git commit` を実行すると、`.github/.gitmessage.txt` の内容が展開されます。

### 設定の削除

設定を削除する際は以下を実行：

```bash
git config --unset commit.template
```

---

<details>
<summary><strong>💡 Cursor を使う場合のエディタ変更方法</strong></summary>

デフォルトでは Vim が開くため、他のエディタに変更するには以下を実行します。

### 1. アプリケーションまでのフルパスを取得

```bash
find /Applications -name "Cursor"
```

### 2. 編集エディタを設定

```bash
git config --global core.editor "「アプリのフルパス」 --wait"
```

### 3. 既存設定を消してから再設定（必要な場合）

```bash
git config --global --unset-all core.editor
git config --global core.editor "「アプリのフルパス」 --wait"
```

### 4. 設定確認

```bash
git config --global --get-all core.editor
```

</details>
