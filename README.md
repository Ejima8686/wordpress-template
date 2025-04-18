# wordpress-template-v3
**Visual Studio Code の DevContainer 機能を活用した WordPress 開発環境テンプレート**です。

## 必要なソフト・拡張機能
以下をローカルにインストールしてください。
- [Docker Desktop](https://www.docker.com/products/docker-desktop)
- [Visual Studio Code](https://code.visualstudio.com/)
- [Node.js（v18 以上推奨）](https://nodejs.org/)
- [Dev Containers](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers)<br>（VS Code で DevContainer を使用するために必須）

## 構成

| 項目 | 内容 |
|------|------|
| WordPress | 最新版 + PHP 8.2 + Apache |
| DB | MariaDB |
| Dev Container | `.devcontainer/` フォルダ |

## 導入

### 1. 初期化スクリプトの実行
```bash
npm run setup:init
```

- `init.mjs` が対話形式で以下を行います：
  - テーマ名の確認（またはリネーム）
  - `.devcontainer/.env` の作成（THEME_NAME, VITE_THEME_NAME）
  - `auth.json` の作成（ACF PRO用ファイル）

### 2. Dev Container の起動
VSCode を使用してコンテナを起動します。<br>
`.devcontainer/init.sh` に基づいて、wordpress環境が構築されます。<br>
開発を再開する場合もこちらを実行してください。

- コマンドパレット（`Cmd+Shift+P`）から  
  **"Dev Containers: Reopen in Container"** を選択

### 3. 開発スタート
ブラウザから WordPressの管理画面にアクセスし、開発を開始してください。  

[http://localhost:8080/wp-admin](http://localhost:8080/wp-admin)

### 4. ローカル環境に戻る（Dev Container の終了）
Dev Container 内での作業を終了し、VS Code がローカル環境に戻ります。

- コマンドパレット（`Cmd+Shift+P`）を開き、  
  **"Dev Containers: Reopen Folder Locally"** を選択

## コミットテンプレートのセットアップ

まずはプロジェクトルートで以下のコマンドを実行し、`.github/.gitmessage.txt`をコミットの初期表示に設定します。

```bash
git init
git config commit.template "$(pwd)/.github/.gitmessage.txt"
```

`git commit`を実行すると、`.github/.gitmessage.txt`の内容が展開します。
コミットメッセージの一貫性を保つために、このルールに従ってコミットメッセージを記述してください。

設定を削除する際は以下を実行してください。

```bash
git config --unset commit.template
```
<br>

<details>
<summary style="font-size: 16px; font-weight: bold;">※エディタがCursorの場合</summary>

VSCode の場合、`git commit`を実行すると既存の設定で`.github/.gitmessage.txt`の内容が VSCode 上で展開されますが、Cursor の場合 Vim で展開されます。
編集エディタを変える場合以下の手順で変更してください。

1. アプリケーションまでのフルパスを取得する

```bash
find /Applications -name "Cursor”
```

2. コミットメッセージを編集するエディタをアプリケーションまでのフルパスで指定

```bash
git config --global core.editor “「アプリケーションまでのフルパス」 -—wait”
```

上記のコマンドでエディタの設定ができずエラーになる場合、以下のコマンドで設定してください。

1. 現在の git の編集エディタの設定を確認

```bash
git config --global --get-all core.editor
```

2. 現在の git の編集エディタの設定を削除

```bash
git config --global --unset-all core.editor
```

3. git の編集エディタを設定

```bash
git config --global core.editor “「アプリケーションまでのフルパス」 -—wait”
```

4. 再度`git config --global --get-all core.editor`を実行し、設定が反映されているか確認。

または、

```bash
git config --global --replace-all core.editor "「アプリケーションまでのフルパス」 --wait”
```

で編集エディタの設定を書き換えられます。
</details>
