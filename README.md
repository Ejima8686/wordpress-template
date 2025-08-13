# wordpress-template

**Visual Studio Code の DevContainer 機能を活用した WordPress 開発環境テンプレート**です。

## 必要なソフト・拡張機能

以下をローカルにインストールしてください。

- [Docker Desktop](https://www.docker.com/products/docker-desktop)
- [Visual Studio Code](https://code.visualstudio.com/)
- [Node.js（v18 以上推奨）](https://nodejs.org/)
- [Dev Containers](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers)<br>（VS Code で DevContainer を使用するために必須）

## 構成

| 項目          | 内容                                                    |
| ------------- | ------------------------------------------------------- |
| WordPress     | 最新版 + PHP 8.2 + Apache                               |
| DB            | MariaDB                                                 |
| Dev Container | `.devcontainer/` フォルダ                               |
| Vite          | `source/` フォルダ内のCSS、JSのホットリロード及びビルド |

## 導入

### 1. 初期化スクリプトの実行

```bash
npm run setup:init
```

- `setup/init.mjs` が対話形式で以下を行います：
  - テーマ名の確認（またはリネーム）
  - `.devcontainer/.env` の作成（THEME_NAME, VITE_THEME_NAME）
  - `auth.json` の作成（ACF PRO用ファイル）

### 2. Dev Container の起動

VSCode を使用してコンテナを起動します。<br>
`.devcontainer/init.sh` に基づいて、wordpress環境が構築されます。<br>
開発を再開する場合もこちらを実行してください。

- コマンドパレット（`Cmd+Shift+P`）から
  **"Dev Containers: Reopen in Container"** を選択

### 3. Vite の起動

コンテナ内で、Viteを起動させます。

```bash
npm run i
npm run dev
```

[http://localhost:8080/](http://localhost:8080/)にアクセスしてください

### 4. 開発スタート

ブラウザから WordPressの管理画面にアクセスし、開発を開始してください。

[http://localhost:8080/wp-admin](http://localhost:8080/wp-admin)<br>

- ユーザー名: `admin`<br>
- パスワード: `password`

### 5. ローカル環境に戻る（Dev Container の終了）

Dev Container 内での作業を終了し、VS Code がローカル環境に戻ります。

- コマンドパレット（`Cmd+Shift+P`）を開き、
  **"Dev Containers: Reopen Folder Locally"** を選択

## データの復元

すでに他開発者がエクスポートしたデータがある場合、データの復元ができます。
`services/wordpress/portal`に以下があることを確認し、コマンドを実行してください。

- `uploads.zip`（アップロードした画像等のファイル）
- `wordpress.sql`（投稿された記事などのデータ）

```bash
npm run import
```

## データのエクスポート

Wordpressのデータをエクスポートできます。以下のデータが出力されます。

- `uploads.zip`（アップロードした画像等のファイル）
- `wordpress.sql`（投稿された記事などのデータ）

```bash
npm run export
```

## 本番用ビルド

次のコマンドを実行すると、ビルド済みのファイルが [テーマ名]/build ディレクトリに出力されます。

```bash
npm run build
```

## フォーマット

```bash
npm run format
```

## ブロックの作成の仕方

```bash
npx scaffdog generate acf-block
```

ACFPRO用のカスタムブロックを作成できます。<br>
対話に沿って生成してください。

- name
  - ブロックの slug を入れてください
- title
  - エディタ側で表示する名前を入れてください。日本語でも OK です
- description
  - エディタ側で表示する詳細文を書いてください
- icon
  - エディタ側で表示するアイコンを書いてください
  - [このサイト](https://developer.wordpress.org/resource/dashicons/)から選んください
  - `dashicons-` というプレフィクスはのぞいて指定してください eg. `dashicons-menu-alt3` なら `menu-alt3`
- category
  - エディタ側で表示するカテゴリを指定してください。
  - text
  - media
  - design
  - widgets
  - theme
  - embed
  - デフォルトは `text`

### 作成したブロックの登録の仕方

1. `mytheme/inc/blocks.php` の　`allowed_block_types_all`の中で、使用するブロックを配列に追加してください。使用できるブロックは[こちら](https://wphelpers.dev/blocks)から確認してください。
2. ACFブロックを追加する場合は、nameに入力した値に `acf/` プレフィクスをつけたものを指定してください。eg. `heading` → `acf/heading`

## DevContainerでのGit操作のためのSSHセットアップ

ただコンテナを立ち上げても、ホストの持つ鍵情報はコンテナに共有されないため、SSH接続ができません。
コンテナ内で`git push`等のGitリモート操作をSSH経由で行えるように設定できます。<br>

- セットアップは必須ではありません。コンテナで作業後、ローカル環境に戻ればGitリモート操作自体は可能です。

- また、このセットアップはGit側に公開鍵を登録済みであり、ホスト側に秘密鍵を所持していることが前提となります。

以下のコマンドを実行してください。

```bash
npm run setup:git
```

- `setup/github/ssh-init.mjs`により、対話形式で`ssh.env`を作成します。**githubの秘密鍵のパスが必要になります。**
  <br>
- `setup/github/ssh.sh`により、ssh-agent を起動し、SSH鍵を ssh-agent に登録します。

セットアップ後、[コンテナを立ち上げ](#2-dev-container-の起動)、以下を実行してssh-agentの起動状況を確認してください。

```bash
ssh-add -l
```

出力例 → `3072 SHA256:xxxx... your-key-name (RSA)`

これでGitのリモート操作が可能になります。

## コミットテンプレートのセットアップ

コミットメッセージの一貫性を保つ手段として、コミットメッセージのテンプレートを用意しています。
使用するには、プロジェクトルートで以下のコマンドを実行し、`.github/.gitmessage.txt`をコミットの初期表示に設定します。

```bash
git config commit.template .github/.gitmessage.txt
```

`git commit`を実行すると、`.github/.gitmessage.txt`の内容が展開します。
設定を削除する際は以下を実行してください。

```bash
git config --unset commit.template
```

<br>

<details>
<summary style="font-size: 16px; font-weight: bold;">※Cursor を使う場合のエディタ変更方法</summary>

デフォルトでは Vim が開くため、他のエディタに変更するには以下を実行します。

1. アプリケーションまでのフルパスを取得する

```bash
find /Applications -name "Cursor"
```

2. 編集エディタを設定

```bash
git config --global core.editor "「アプリのフルパス」 --wait"
```

既存設定を消してから再設定したい場合:

```bash
git config --global --unset-all core.editor
git config --global core.editor "「アプリのフルパス」 --wait"
```

設定確認:

```bash
git config --global --get-all core.editor
```

</details>
