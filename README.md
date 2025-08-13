# WordPress Template

**Visual Studio Code の DevContainer 機能を活用した WordPress 開発環境テンプレート**

## 目次

- [必要なソフト・拡張機能](#必要なソフト・拡張機能)
- [構成](#構成)
- [導入](#導入)
- [データの復元・エクスポート](#データの復元・エクスポート)
- [開発コマンド](#開発コマンド)
- [ブロックの作成](#ブロックの作成)
- [SSHセットアップ](#sshセットアップ)
- [コミットテンプレート](#コミットテンプレート)

## 必要なソフト・拡張機能

以下をローカルにインストールしてください：

- [Docker Desktop](https://www.docker.com/products/docker-desktop)
- [Visual Studio Code](https://code.visualstudio.com/)
- [Node.js（v18 以上推奨）](https://nodejs.org/)
- [Dev Containers](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers)（VS Code で DevContainer を使用するために必須）

## 構成

| 項目               | 内容                               |
| ------------------ | ---------------------------------- |
| **CMS**            | WordPress最新版 + PHP 8.2 + Apache |
| **DB**             | MariaDB                            |
| **開発環境**       | Docker + Dev Container             |
| **フロントエンド** | Vite + Tailwind CSS + Alpine.js    |
| **開発ツール**     | WP-CLI、Composer、Node.js v20      |

## 導入

### 1. 初期化スクリプトの実行

```bash
npm run setup:init
```

`setup/init.mjs` が対話形式で以下を行います：

- テーマ名の確認（またはリネーム）
- `.devcontainer/.env` の作成（THEME_NAME, VITE_THEME_NAME）
- `auth.json` の作成（ACF PRO用ファイル）

### 2. Dev Container の起動

VSCode を使用してコンテナを起動します。

`.devcontainer/init.sh` に基づいて、WordPress環境が構築されます。開発を再開する場合もこちらを実行してください。

**手順：**

1. コマンドパレット（`Cmd+Shift+P`）を開く
2. **"Dev Containers: Reopen in Container"** を選択

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

### 5. ローカル環境に戻る（Dev Container の終了）

Dev Container 内での作業を終了し、VS Code がローカル環境に戻ります。

**手順：**

1. コマンドパレット（`Cmd+Shift+P`）を開く
2. **"Dev Containers: Reopen Folder Locally"** を選択

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

## SSHセットアップ

コンテナ内で `git push` 等のGitリモート操作をSSH経由で行えるように設定できます。

> **注意：**
>
> - セットアップは必須ではありません。コンテナで作業後、ローカル環境に戻ればGitリモート操作自体は可能です
> - このセットアップはGit側に公開鍵を登録済みであり、ホスト側に秘密鍵を所持していることが前提です

### セットアップ手順

**1. セットアップコマンドの実行**

```bash
npm run setup:git
```

**2. 実行される処理**

- `setup/github/ssh-init.mjs`：対話形式で `ssh.env` を作成（GitHubの秘密鍵のパスが必要）
- `setup/github/ssh.sh`：ssh-agent を起動し、SSH鍵を ssh-agent に登録

**3. 動作確認**
[コンテナを立ち上げ](#2-dev-container-の起動)後、以下を実行してssh-agentの起動状況を確認：

```bash
ssh-add -l
```

**出力例：** `3072 SHA256:xxxx... your-key-name (RSA)`

これでGitのリモート操作が可能になります。

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
