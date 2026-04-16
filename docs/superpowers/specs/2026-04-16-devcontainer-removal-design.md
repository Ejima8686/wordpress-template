# DevContainer 削除設計

**日付:** 2026-04-16
**関連 Issue:** Ejima8686/wordpress-template#17

---

## 背景

VS Code の DevContainer は Docker Compose だけで代替できる機能しか追加していない。削除することで構成をシンプルにし、エディタ非依存の開発環境にする。

---

## 変更の全体像

```
削除:  .devcontainer/devcontainer.json
移動:  .devcontainer/docker-compose.yml → docker-compose.yml（プロジェクトルート）
移動:  .devcontainer/.env               → .env（プロジェクトルート）※.gitignore 済み
移動:  .devcontainer/auth.json          → auth.json（プロジェクトルート）※.gitignore 済み
新規:  .vscode/extensions.json
残す:  .devcontainer/Dockerfile
残す:  .devcontainer/init.sh
```

---

## 各ファイルの変更詳細

### 1. `devcontainer.json` を削除

`.devcontainer/devcontainer.json` を削除する。

### 2. `docker-compose.yml` をプロジェクトルートへ移動

`.devcontainer/docker-compose.yml` を `docker-compose.yml`（プロジェクトルート）へ移動し、以下を修正する。

**ビルドコンテキストの変更:**
```yaml
# Before
build:
  context: ./
  dockerfile: Dockerfile

# After
build:
  context: .devcontainer/
  dockerfile: Dockerfile
```

**ボリュームパスの変更（`../` → `./`）:**
```yaml
# Before
- "./.env:/var/www/html/.env"
- "./auth.json:/var/www/html/auth.json"
- "../${THEME_NAME}:/var/www/html/wp-content/themes/${THEME_NAME}"
- "../plugins:/var/www/html/wp-content/plugins"
- "../package.json:/var/www/html/package.json"
- "../package-lock.json:/var/www/html/package-lock.json"
- "../composer.json:/var/www/html/composer.json"
- "../:/workspaces"
- "../wordpress/wp-config.php:/var/www/html/wp-config.php"
- "../services/wordpress/portal:/var/www/html/portal"
- "../standalone/app:/var/www/app"
- "../standalone/lp:/var/www/html/lp"

# After
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
- "./standalone/app:/var/www/app"
- "./standalone/lp:/var/www/html/lp"
```

**`env_file` パスの変更:**
```yaml
# Before
env_file:
  - ./.env

# After
env_file:
  - .env
```

### 3. `setup/init.mjs` のパス修正

`.devcontainer/` プレフィックスをルート相対パスに変更する。

```js
// Before
const envSampleFilePath = path.resolve(root, ".devcontainer/.env.sample");
const envFilePath = path.resolve(root, ".devcontainer/.env");
// ...
const authJsonFilePath = path.resolve(root, ".devcontainer/auth.json");

// After
const envSampleFilePath = path.resolve(root, ".env.sample");
const envFilePath = path.resolve(root, ".env");
// ...
const authJsonFilePath = path.resolve(root, "auth.json");
```

### 4. `init.sh` に `npm install` を追記

`wait` の前に npm install を追加する。

```bash
# 追記箇所（wait の直前）
cd /workspaces && npm install

wait
```

### 5. `.vscode/extensions.json` を新規作成

`devcontainer.json` に定義されていた拡張機能リストを移行する。

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

### 6. `README.md` の更新

- タイトル・説明文から「DevContainer」の記述を削除
- 「必要なソフト」から VS Code と Dev Containers 拡張を削除（または任意へ変更）
- 「導入」セクションの手順2を `docker compose up` ベースに書き換え
- 「構成」テーブルの「開発環境」を `Docker + Docker Compose` に変更

### 7. `README_standalone.md` の更新

`.devcontainer/docker-compose.yml` への参照を `docker-compose.yml` に変更する。

### 8. `.docs/` ドキュメントの更新

**`.docs/00_ワークスペースの設計/README.md`:**
- ディレクトリ構成から `devcontainer.json` を削除

**`.docs/01_初期化から開発スタートまでのフロー/README.md`:**
- DevContainer 起動フロー（手順2）を `docker compose up` ベースに書き換え
- `devcontainer.json` の読み込みフロー説明を削除

---

## 起動手順の変化

```bash
# Before（DevContainer）
# VS Code コマンドパレット → "Dev Containers: Reopen in Container"

# After（Docker Compose）
docker compose up
```

---

## 影響範囲

- 機能的な変化なし（WordPress 自動セットアップは init.sh が引き続き担当）
- VS Code 拡張は `.vscode/extensions.json` で推奨表示（手動インストール）
- `npm install` は init.sh に追記することで自動化を維持
