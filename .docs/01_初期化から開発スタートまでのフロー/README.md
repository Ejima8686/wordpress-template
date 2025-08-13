# 初期化から開発スタートまでのフロー

親記事: [💼ワークスペースの設計](../00_ワークスペースの設計/README.md)

---

## 目次

- [**1. 初期化スクリプトの実行**](#1-初期化スクリプトの実行)
- [**2. Dev Container の起動**](#2-dev-container-の起動)
  - [コンテナが立ち上がるまで。](#コンテナが立ち上がるまで)

---

> 👉 👉 👉 👉 👉
> [READMEの導入](https://github.com/nevers-jp/wordpress-template-v3/blob/main/README.md)の手順に沿って、初期化から開発まで、
> **どのファイルがどの様な仕組みで動いているか**を説明します。
> 👈👈👈👈👈👈

## **1. 初期化スクリプトの実行**

```markdown
npm run setup:init
```

1. `package.json` のscriptsの記述に基づき、`npm ci`が走ります。[@inquirer/prompts](https://www.npmjs.com/package/@inquirer/prompts)をインストールします。これはセットアップを対話式で出力するのに必要です。

2. 続いて、`node init.mjs` により、`/init.mjs`の`main()`が実行されます。ターミナル上で対話式セットアップが開始されます。
   セットアップの主な要件は以下です。

| 要件                                 | 対応する関数                             | 説明                                                                                                                                                                                                  |
| ------------------------------------ | ---------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| .envを作成                           | `generateEnvFile()`<br>`updateEnvFile()` | このファイルは環境変数を定義して、dockerに渡すのに使用します。<br><br>セットアップ時点では、`docker-compose.yml` > environment , env_file にて参照されています。                                      |
| auth.jsonを作成                      | `generateAuthJson()`                     | [ACF PROのインストールに必須なファイル](https://arc.net/l/quote/pfqrdpzc)です。                                                                                                                       |
| テーマディレクトリを任意の名前にする | `renameTheme()`                          | テーマの名前を任意に変更できる様にしています。<br><br>※テーマディレクトリの名前は環境変数として登録されます。常に環境変数とテーマ名は同じである必要があるため、**セットアップ後には変更できません。** |

これでコンテナを立ち上げる前準備が整いました。

## **2. Dev Container の起動**

```plaintext
コマンドパレット（`Cmd+Shift+P`）から
  "Dev Containers: Reopen in Container" を選択
```

<details open="">
<summary style="font-weight:600;font-size:1.25em;line-height:1.3;margin:0">コンテナが立ち上がるまで。</summary>
<div class="indented">

1. **VS Codeで devcontainer を起動。**
   👉 プロジェクトルートにある`.devcontainer/devcontainer.json` が自動的に検出されます。

2. **devcontainer.jsonを読み、docker-compose.yml の サービスを確認。**

```json
"dockerComposeFile": "docker-compose.yml",
"service": "wordpress"
```

👉 dockerの**「wordpress」サービスを**devcontainerの**起動対象として使用します。**

3. **Dockerfile を参照して WordPress イメージをビルド**

```yaml
build:
  context: ./
  dockerfile: Dockerfile　⇦ Dokerfileを参照し、カスタムしたコンテナを作るという指示
```

👉 **Dockerfile の内容（PHPやNode.js入り）に基づいて、独自の WordPress 開発用イメージをビルドします。**

```yaml
COPY init.sh /usr/local/bin/　⇦ コンテナ内にinit.shをコピー
RUN chmod +x /usr/local/bin/init.sh　⇦ 権限付与

ENTRYPOINT ["/usr/local/bin/init.sh"]　⇦ コンテナ起動時に絶対に実行されるスクリプトを指定
```

**👉 コンテナの/usr/local/bin に init.shをコピーします**

4. **docker-compose.yml を参照して WordPressコンテナとDBコンテナ（MariaDB）を起動**
   - サービス名：wordpress、db
   - wordpressポート 8080 → コンテナの 80 にマッピング

```yaml
    depends_on: ⇦ DBをdbコンテナと繋ぎ込み
      - db
    ports:　⇦ 8080ポートをコンテナの 80 にマッピング
      - 8080:80
```

5. **/usr/local/bin/init.shの処理が実行される。**
   主にwp-config.phpの初期化を行います。

**この過程がないと、wordpressの初期設定を管理画面で行わなければならず、スムーズに開発に移ることができない＆開発者によって設定がバラついてしまいます。**

- wp-config.php の自動生成
- WordPress のインストールと初期設定（パーマリンク、日本語化など）
- よく使うプラグインのインストール
- ACF PROのアクティベート

**Wordpress関連の処理には**[wp-cli](https://wp-cli.org/ja/)**を使用しています。**

| 主な処理                                                                                | 説明                                                                                                                                                          |
| --------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| docker-entrypoint.sh apache2-foreground &                                               | ① docker公式のWordPress初期化スクリプト起動。<br/>② Apache HTTP サーバーをフォアグラウンドモードで起動。                                                      |
| wp core download [https://wordpress.org/latest.zip](https://wordpress.org/latest.zip) … | 最新バージョンのWordpressをダウンロード。                                                                                                                     |
| wp config create …                                                                      | docker-entrypoint.shで作られたwp-config.phpを新たに上書き。<br/>これでカスタムセットアップが可能になります。                                                  |
| wp core install …                                                                       | オプションで指定している通りの初期セットアップを行います。                                                                                                    |
| echo "define( 'ACF_PRO_LICENSE', '${ACF_PRO_KEY}' );" >> "$root_path/wp-config.php"     | [ACF PROのライセンスをアクティベートする](https://www.advancedcustomfields.com/resources/how-to-activate/#activating-acf-pro-in-wp-configphp)ための記述です。 |

6. **devcontainer.jsonのworkspaceFolderのディレクトリを作業ディレクトリとしてVS Codeが開く**

```yaml
"workspaceFolder": "/workspaces",
```

👉 VS Codeの初期ファイルビューやターミナルの初期地点が`/workspaces` になります。

7. **devcontainer.jsonに記述された拡張機能が自動インストールされる**

```json
    "customizations": {
        "vscode": {
            "extensions": [
                "adrianwilczynski.alpine-js-intellisense",
                "csstools.postcss",
                "esbenp.prettier-vscode",
                "bradlc.vscode-tailwindcss",
                "mblode.twig-language-2"
            ]
        }
    },
```

8. **devcontainer.jsonの**`"postCreateCommand"`** のコマンドを実行**

```javascript
  ...

	"postCreateCommand": "cd /workspaces && npm install"
```

念の為ワークスペースに移動し、パッケージインストールをしています。
**viteの都合上、コンテナビルド後に**`npm i`**をする必要があります。**

9. **🚗💨 http://localhost:8080 にアクセスして WordPress にログイン！**

</div>
</details>
