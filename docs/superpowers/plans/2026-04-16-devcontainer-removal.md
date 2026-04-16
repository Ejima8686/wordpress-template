# DevContainer 削除 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** `devcontainer.json` を削除し、`docker-compose.yml` をプロジェクトルートへ移動することで、エディタ非依存の `docker compose up` だけで起動できるシンプルな開発環境にする。

**Architecture:** `.devcontainer/` からは `Dockerfile` と `init.sh` のみを残し、`docker-compose.yml` はプロジェクトルートへ移動する。`.env` と `auth.json` もルートへ移動（`.gitignore` は既にルートを対象にしている）。VS Code 拡張機能は `.vscode/extensions.json` で代替。

**Tech Stack:** Docker Compose、Node.js（setup/init.mjs）、Bash（init.sh）

---

## ファイルマップ

| 操作 | ファイル |
|---|---|
| 削除 | `.devcontainer/devcontainer.json` |
| 移動・修正 | `.devcontainer/docker-compose.yml` → `docker-compose.yml` |
| 修正 | `setup/init.mjs` |
| 修正 | `.devcontainer/init.sh` |
| 新規作成 | `.vscode/extensions.json` |
| 修正 | `README.md` |
| 修正 | `README_standalone.md` |
| 修正 | `.docs/00_ワークスペースの設計/README.md` |
| 修正 | `.docs/01_初期化から開発スタートまでのフロー/README.md` |

---

### Task 1: devcontainer.json の削除と .vscode/extensions.json の作成

**Files:**
- Delete: `.devcontainer/devcontainer.json`
- Create: `.vscode/extensions.json`

- [ ] **Step 1: devcontainer.json を削除する**

```bash
rm .devcontainer/devcontainer.json
```

- [ ] **Step 2: .vscode/extensions.json を作成する**

ファイル `.vscode/extensions.json` を新規作成し、以下の内容を書き込む：

```json
{
  "recommendations": [
    "adrianwilczynski.alpine-js-intellisense",
    "csstools.postcss",
    "esbenp.prettier-vscode",
    "bradlc.vscode-tailwindcss",
    "mblode.twig-language-2",
    "eamodio.gitlens",
    "mhutchie.git-graph"
  ]
}
```

- [ ] **Step 3: ファイルの存在を確認する**

```bash
ls .devcontainer/
# 出力に devcontainer.json が含まれていないこと
# Dockerfile  docker-compose.yml  init.sh  が残っていること

cat .vscode/extensions.json
# 上記 JSON が出力されること
```

- [ ] **Step 4: コミット**

```bash
git add -A
git commit -m "✨ Feat: devcontainer.json を削除し .vscode/extensions.json を作成"
```

---

### Task 2: docker-compose.yml をプロジェクトルートへ移動

**Files:**
- Delete: `.devcontainer/docker-compose.yml`
- Create: `docker-compose.yml`

- [ ] **Step 1: プロジェクトルートに docker-compose.yml を作成する**

ファイル `docker-compose.yml` を新規作成し、以下の内容を書き込む（ボリュームパスを `../` → `./` に変更、`build.context` を `.devcontainer/` に変更）：

```yaml
name: "${THEME_NAME}"
services:
  wordpress:
    container_name: wp-${THEME_NAME}
    build:
      context: .devcontainer/
      dockerfile: Dockerfile
    depends_on:
      - db
      - mailpit
    ports:
      - 8080:80
      - 5137:5137 # standalone LP用Vite開発サーバー（WordPressのViteは3000）
    volumes:
      - "./.env:/var/www/html/.env"
      - "./auth.json:/var/www/html/auth.json"

      - "./${THEME_NAME}:/var/www/html/wp-content/themes/${THEME_NAME}"
      - "./plugins:/var/www/html/wp-content/plugins"

      - "./package.json:/var/www/html/package.json"
      - "./package-lock.json:/var/www/html/package-lock.json"
      - "./composer.json:/var/www/html/composer.json"

      - ".:/workspaces"

      - "./wordpress/wp-config.php:/var/www/html/wp-config.php"
      - "./services/wordpress/portal:/var/www/html/portal"

      # WordPress外のサイトを配置
      - "./standalone/app:/var/www/app" # アプリケーション
      - "./standalone/lp:/var/www/html/lp" # フロントエンド（増やしていく）

    environment:
      IS_DEVELOPMENT: 1 # Docker環境であることを示す
      # DEBUG_MODE: 1 # 通常は.envから設定する
      COMPOSER_ALLOW_SUPERUSER: 1
      COMPOSER_NO_INTERACTION: 1
      WORDPRESS_DEBUG: 1
      WORDPRESS_THEME_NAME: "${THEME_NAME}"
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_PORT: 3306
      WORDPRESS_DB_USER: wordpress
      WORDPRESS_DB_PASSWORD: wordpress
      WORDPRESS_DB_NAME: wordpress
      ACF_PRO_KEY: "${ACF_PRO_KEY}"
    env_file:
      - .env

  db:
    container_name: db-${THEME_NAME}
    image: mariadb:11.4
    ports:
      - "3306:3306"
    environment:
      MYSQL_ROOT_PASSWORD: wordpress
      MYSQL_DATABASE: wordpress
      MYSQL_USER: wordpress
      MYSQL_PASSWORD: wordpress
    volumes:
      - db:/var/lib/mysql

  mailpit:
    container_name: mailpit-${THEME_NAME}
    image: axllent/mailpit:v1.21
    ports:
      - 1025:1025 #SMTP
      - 8025:8025 #Web UI
    restart: unless-stopped
    environment:
      MP_MAX_MESSAGES: 5000
      MP_DATABASE: /data/mailpit.db
      MP_SMTP_AUTH_ACCEPT_ANY: 1
      MP_SMTP_AUTH_ALLOW_INSECURE: 1
    volumes:
      - mailpit:/data

volumes:
  wordpress:
  db:
  mailpit:
```

- [ ] **Step 2: 元の docker-compose.yml を削除する**

```bash
rm .devcontainer/docker-compose.yml
```

- [ ] **Step 3: 構文チェック**

```bash
docker compose config
# エラーが出ないこと（THEME_NAME 未定義の警告は無視してよい）
```

- [ ] **Step 4: コミット**

```bash
git add -A
git commit -m "🚀 Feat: docker-compose.yml をプロジェクトルートへ移動しボリュームパスを修正"
```

---

### Task 3: setup/init.mjs のパス修正

**Files:**
- Modify: `setup/init.mjs:10-11,110,162`

- [ ] **Step 1: envSampleFilePath・envFilePath のパスを修正する**

`setup/init.mjs` の10〜11行目を以下に変更する：

```js
// Before
const envSampleFilePath = path.resolve(root, ".devcontainer/.env.sample");
const envFilePath = path.resolve(root, ".devcontainer/.env");

// After
const envSampleFilePath = path.resolve(root, ".env.sample");
const envFilePath = path.resolve(root, ".env");
```

- [ ] **Step 2: authJsonFilePath のパスを修正する**

`setup/init.mjs` の110行目（`generateAuthJson` 関数内）を以下に変更する：

```js
// Before
const authJsonFilePath = path.resolve(root, ".devcontainer/auth.json");

// After
const authJsonFilePath = path.resolve(root, "auth.json");
```

- [ ] **Step 3: 完了メッセージを修正する**

`setup/init.mjs` の162行目を以下に変更する：

```js
// Before
console.log("\n✅ 初期化が完了しました！Dev Container を起動して、開発を開始しましょう！");

// After
console.log("\n✅ 初期化が完了しました！docker compose up を実行して、開発を開始しましょう！");
```

- [ ] **Step 4: コミット**

```bash
git add setup/init.mjs
git commit -m "🔧 Fix: setup/init.mjs の .devcontainer/ パス依存を削除"
```

---

### Task 4: init.sh に npm install を追記

**Files:**
- Modify: `.devcontainer/init.sh:68-76`

- [ ] **Step 1: wait の前に npm install を追記する**

`.devcontainer/init.sh` の末尾付近を以下に変更する：

```bash
# Before
chown www-data:www-data -R /var/www/html/wp-content

wait

# After
chown www-data:www-data -R /var/www/html/wp-content

cd /workspaces && npm install

wait
```

- [ ] **Step 2: コミット**

```bash
git add .devcontainer/init.sh
git commit -m "✨ Feat: init.sh に npm install を追記"
```

---

### Task 5: README.md の更新

**Files:**
- Modify: `README.md`

- [ ] **Step 1: タイトル説明文を変更する**

1行目の説明文を変更する：

```markdown
# Before
**Visual Studio Code の DevContainer 機能を活用した WordPress 開発環境テンプレート**

# After
**Docker Compose を使った WordPress 開発環境テンプレート**
```

- [ ] **Step 2: 必要なソフト・拡張機能セクションを更新する**

```markdown
# Before
- [Docker Desktop](https://www.docker.com/products/docker-desktop)
- [Visual Studio Code](https://code.visualstudio.com/)
- [Node.js（v18 以上推奨）](https://nodejs.org/)
- [Dev Containers](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers)（VS Code で DevContainer を使用するために必須）

# After
- [Docker Desktop](https://www.docker.com/products/docker-desktop)
- [Node.js（v18 以上推奨）](https://nodejs.org/)
```

- [ ] **Step 3: 構成テーブルの開発環境を更新する**

```markdown
# Before
| **開発環境**       | Docker + Dev Container             |

# After
| **開発環境**       | Docker + Docker Compose            |
```

- [ ] **Step 4: 導入セクション・ステップ1の説明を更新する**

```markdown
# Before
- `.devcontainer/.env` の作成（THEME_NAME, VITE_THEME_NAME）
- `auth.json` の作成（ACF PRO用ファイル）

# After
- `.env` の作成（THEME_NAME, VITE_THEME_NAME）
- `auth.json` の作成（ACF PRO用ファイル）
```

- [ ] **Step 5: 導入セクション・ステップ2を docker compose up に書き換える**

「### 2. Dev Container の起動」セクション全体を以下に置き換える：

```markdown
### 2. コンテナの起動

プロジェクトルートで以下を実行します。

```bash
docker compose up
```

`.devcontainer/init.sh` に基づいて WordPress 環境が構築されます。開発を再開する場合もこちらを実行してください。
```

- [ ] **Step 6: 「Dev Container の終了」セクションを削除する**

「### 5. ローカル環境に戻る（Dev Container の終了）」セクション（手順1〜2を含む）を丸ごと削除する。

- [ ] **Step 7: SSHセットアップ内の「コンテナを立ち上げ」リンクを修正する**

```markdown
# Before
[コンテナを立ち上げ](#2-dev-container-の起動)後、以下を実行してssh-agentの起動状況を確認：

# After
[コンテナを立ち上げ](#2-コンテナの起動)後、以下を実行してssh-agentの起動状況を確認：
```

- [ ] **Step 8: コミット**

```bash
git add README.md
git commit -m "📝 Docs: README.md を docker compose up ベースに更新"
```

---

### Task 6: README_standalone.md の更新

**Files:**
- Modify: `README_standalone.md:25,35-36,94`

- [ ] **Step 1: docker-compose.yml のパス参照を更新する**

```markdown
# Before（25行目付近）
まず、`.devcontainer/docker-compose.yml` にスタンドアロンアプリ用のディレクトリを切ります。

# After
まず、`docker-compose.yml` にスタンドアロンアプリ用のディレクトリを切ります。
```

- [ ] **Step 2: コードブロック内のボリュームパスを更新する**

```yaml
# Before
- "../standalone/app:/var/www/app" # アプリケーション
- "../standalone/lp:/var/www/html/lp" # フロントエンド（増やしていく）

# After
- "./standalone/app:/var/www/app" # アプリケーション
- "./standalone/lp:/var/www/html/lp" # フロントエンド（増やしていく）
```

- [ ] **Step 3: DevContainer への言及を更新する**

```markdown
# Before（94行目付近）
まず、親となるWebサーバーは通常のwordpress起動サーバーを使用しますので、いつも通りコンテナを立ち上げてください。  
ここからはDevcontainer内で作業を進めてOKです。

# After
まず、親となるWebサーバーは通常のwordpress起動サーバーを使用しますので、いつも通りコンテナを立ち上げてください。
```

- [ ] **Step 4: コミット**

```bash
git add README_standalone.md
git commit -m "📝 Docs: README_standalone.md の devcontainer 参照を修正"
```

---

### Task 7: .docs/00 の更新

**Files:**
- Modify: `.docs/00_ワークスペースの設計/README.md`

- [ ] **Step 1: ディレクトリ構成から devcontainer.json を削除する**

```markdown
# Before
├── .devcontainer
│   ├── .env
│   ├── auth.json
│   ├── devcontainer.json
│   ├── docker-compose.yml
│   ├── Dockerfile
│   └── init.sh

# After
├── .devcontainer
│   ├── Dockerfile
│   └── init.sh
├── .vscode
│   └── extensions.json
```

また、ルートの `.env` と `auth.json` をディレクトリ構成に追記する：

```markdown
# Before（ルート部分）
├── .gitignore
├── composer.json

# After（ルート部分）
├── .env
├── auth.json
├── docker-compose.yml
├── .gitignore
├── composer.json
```

- [ ] **Step 2: コミット**

```bash
git add .docs/00_ワークスペースの設計/README.md
git commit -m "📝 Docs: ワークスペース設計ドキュメントのディレクトリ構成を更新"
```

---

### Task 8: .docs/01 の更新

**Files:**
- Modify: `.docs/01_初期化から開発スタートまでのフロー/README.md`

- [ ] **Step 1: ステップ2の見出しを変更する**

```markdown
# Before
## **2. Dev Container の起動**

```plaintext
コマンドパレット（`Cmd+Shift+P`）から
  "Dev Containers: Reopen in Container" を選択
```

# After
## **2. コンテナの起動**

```bash
docker compose up
```
```

- [ ] **Step 2: DevContainer の起動フロー詳細説明を書き換える**

`<details open="">` ブロック内のコンテンツを以下に書き換える：

```markdown
<details open="">
<summary style="font-weight:600;font-size:1.25em;line-height:1.3;margin:0">コンテナが立ち上がるまで。</summary>
<div class="indented">

1. **プロジェクトルートで `docker compose up` を実行する。**
   👉 `docker-compose.yml` が自動的に検出されます。

2. **`docker-compose.yml` のビルドセクションを参照して WordPress イメージをビルド**

```yaml
build:
  context: .devcontainer/
  dockerfile: Dockerfile
```

👉 `.devcontainer/Dockerfile` の内容（PHP・Node.js入り）に基づいて独自の WordPress 開発用イメージをビルドします。

```yaml
COPY init.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/init.sh

ENTRYPOINT ["/usr/local/bin/init.sh"]
```

**👉 コンテナ起動時に `.devcontainer/init.sh` が自動実行されます。**

3. **WordPress コンテナと DB コンテナ（MariaDB）・Mailpit を起動**
   - サービス名：wordpress、db、mailpit
   - wordpress ポート 8080 → コンテナの 80 にマッピング

4. **`/usr/local/bin/init.sh` の処理が実行される。**
   主に wp-config.php の初期化を行います。

   - wp-config.php の自動生成
   - WordPress のインストールと初期設定（パーマリンク、日本語化など）
   - よく使うプラグインのインストール
   - ACF PRO のアクティベート
   - `npm install` の実行（`/workspaces`）

5. **🚗💨 http://localhost:8080 にアクセスして WordPress にログイン！**

</div>
</details>
```

- [ ] **Step 3: コミット**

```bash
git add .docs/01_初期化から開発スタートまでのフロー/README.md
git commit -m "📝 Docs: 初期化フローのドキュメントを docker compose up ベースに更新"
```
