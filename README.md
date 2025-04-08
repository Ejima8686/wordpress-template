# wordpress-template-v3
wordpressのテーマ開発用テンプレートです。

## 構成

| 項目 | 内容 |
|------|------|
| WordPress | PHP 8.2 + Apache |
| DB | MariaDB |
| Dev Container | `.devcontainer/` フォルダ |
| テーマ | `mytheme → セットアップ時に任意のテーマ名にリネーム` |

## 導入

### 1. 初期化スクリプトの実行
```bash
npm run setup:init
```

- `init.mjs` が対話形式で以下を行います：
  - テーマ名の確認（またはリネーム）
  - `.devcontainer/env` の作成（THEME_NAME, VITE_THEME_NAME）
  - `auth.json` の作成（ACF PRO キー入力）
---

### 2. Dev Container の起動
VSCode を使用してコンテナを起動：

- コマンドパレット（`Cmd+Shift+P`）から  
  **"Dev Containers: Reopen in Container"** を選択
---

### 3. 開発スタート
- テーマの作業フォルダ：  
  `/var/www/html/wp-content/themes/$THEME_NAME`
- ブラウザから WordPress を確認：  
  [http://localhost:8080](http://localhost:8080)

---


