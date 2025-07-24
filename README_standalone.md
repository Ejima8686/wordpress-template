# standalone

WordPress外に設置する、独立したLPやフォームの開発環境です。

アプリケーションはいくつでも増やせるようにしてあります。

## 技術スタック

- [Slim（PHPフレームワーク）](https://www.slimframework.com/docs/v4/)
  - [CSRF（CSRF対策）](https://github.com/slimphp/Slim-Csrf)
  - [Twig（テンプレートエンジン）](https://twig.symfony.com/doc/3.x/)
- [PHP DI（依存性注入）](https://www.slimframework.com/docs/v4/concepts/di.html)
- [Dotenv（環境変数）](https://github.com/vlucas/phpdotenv)
- [Respect（バリデーション）](https://respect-validation.readthedocs.io/en/latest/)
- [PHPMailer（メーラー）](https://github.com/PHPMailer/PHPMailer)
- [Mailpit（メール検証サーバー）](https://mailpit.axllent.org/)
- [Vite（フロント開発環境）](https://vite.dev/guide/)
  - [TailwindCSS](https://v2.tailwindcss.com/docs)
  - [AlpineJS](https://alpinejs.dev/)

## 初期設定

### docker-compose.ymlの調整

まず、`.devcontainer/docker-compose.yml` にスタンドアロンアプリ用のディレクトリを切ります。デフォルトでは `lp` という名前で切ってあります。

**この時、非公開領域に `app/lp` ディレクトリを配置し、公開領域に `lp` ディレクトリが配置されることに注意してください。**

リリース時も `app` 以下を 非公開領域に設置し、 `lp` を公開領域に設置する想定です。

非公開領域の `app` には **Slimアプリケーション**と **viteの開発環境**が入っています。

```yml
# WordPress外のサイトを配置
- "../standalone/app:/var/www/app" # アプリケーション
- "../standalone/lp:/var/www/html/lp" # フロントエンド（増やしていく）
```

### vite.config.tsの調整

`standalone/app/lp/vite/vite.confit.ts` を調整します。

`/standalone/app/lp/vite/` から `/standalone/lp/dist/` にビルドしたいので、以下のように設定します。

```typescript
build: {
	outDir: "../../../lp/dist", // 出力先を指定
	...
},
```

### 環境変数の設定

`standalone/app/lp/config/` 内にサンプルが配置されています。開発環境では、 `.env.dev.sample` を `.env` として使用してください。

あらかじめmailpitとの連携設定が書かれています。

mailpitは `http://localhost:8025` で WebUI が表示されます。

|     変数名     | 説明                                                                                           |
| :------------: | :--------------------------------------------------------------------------------------------- |
| ERROR_LOG_PATH | エラーログの出力先パスを設定します。                                                           |
|   DEBUG_MODE   | 開発モード、開発中やデバッグ時のみ有効にする 0 / 1                                             |
| PROJECT_DOMAUN | グローバルにURLを解決したい時に使います。 開発環境、ステージング環境、本番環境でそれぞれ設定。 |
|   SMTP_HOST    | SMTPサーバーのホスト名。Mailpitの場合はdocker-composeのサービス名でOK。                        |
|   SMTP_PORT    | SMTPサーバーのポート番号。Mailpitのデフォルトは1025。                                          |
|   SMTP_USER    | SMTP認証ユーザー名。Mailpitは認証不要なので空欄でOK。                                          |
| SMTP_PASSWORD  | SMTP認証パスワード。Mailpitは認証不要なので空欄でOK。                                          |
|   MAIL_FROM    | 送信元メールアドレス。実際の送信者として表示されるアドレス。                                   |
|   MAIL_ADMIN   | 管理者（通知先）メールアドレス。お問い合わせ通知などの宛先に使用。                             |
|   SMTP_AUTH    | SMTP認証を使うかどうか。Mailpitは不要なのでfalse。                                             |
|  SMTP_SECURE   | 暗号化方式。Mailpitはfalse（暗号化なし）。本番SMTPではtlsやsslを指定することも。               |

### index.phpの調整

非公開領域にある `bootstrap.php` を探索して起動します。`bootstrap.php` の探索さえ上手くいけば、.envの読み込みなどは `bootstrap.php` からの相対パスで解決します。

```php
$bootstrap_candidates = [
	/* 絶対パスでの探索（環境に合わせて設定） */
	"/home/account_name/app/lp/bootstrap.php", #さくら、XServerの場合
	"/var/www/app/lp/bootstrap.php", #Docker開発環境の場合
	/* 相対パスでの探索（フォールバック） */
	__DIR__ . "/../../../../app/lp/bootstrap.php",
	__DIR__ . "/../../../app/lp/bootstrap.php",
	__DIR__ . "/../../app/lp/bootstrap.php",
	__DIR__ . "/../app/lp/bootstrap.php",
];
```

## 開発環境の実行

まず、親となるWebサーバーは通常のwordpress起動サーバーを使用しますので、いつも通りコンテナを立ち上げてください。  
ここからはDevcontainer内で作業を進めてOKです。

### 開発環境のインストール（初回のみ）

まず、composerのインストール。

```bash
$ cd standalone/app/lp
$ composer install
```

つづいて、viteのインストール。

```bash
$ cd vite # standalone/app/lp/vite
$ npm install
```

※ Vite7系統ではNode20系以上が要求されるなどあるので、適宜メンテナンスが必要です。

### 開発環境の立ち上げ

viteの開発環境を立ち上げますが、この時に開発したいアプリケーションのviteディレクトリまで移動します。

```bash
$ cd standalone/app/lp/vite
$ npm run dev
```

Viteサーバーが立ち上がるので、 `http://localhost:5137/lp/` で確認してください。このポートは`localhost:8080` をプロキシしたViteの開発確認用になります。

## ビルドとリリース

リリースする際は、静的アセットをビルドしておく必要があります。

```bash
$ cd standalone/app/lp/vite
$ npm run build
```

これで `standalone/lp/dist` に静的アセットがビルドされたはずなので、 `standalone/app/lp` 内を非公開領域に配置し `standalone/lp`、内を公開領域に配置すればリリース完了です。

## アプリケーションの構造

### standalone/lp/

公開領域に配置するファイル群です。 distに入った静的アセット群や `.htacess`、エントリポイントとなる `index.php` を配置します。

`.htaccess` で `index.php` にアクセスを集め、 `index.php` は `/app/lp/bootstrap.php` を探索して起動します。

### standalone/app/lp/

起動ファイルとなる `bootstrap.php` のほか、アプリケーションと開発環境を配置します。

### standalone/app/lp/config/

環境変数を配置します。

### standalone/app/lp/log/

PHPサーバーで発生したエラー（`error_log()`）を `error_log.log` に吐き出していきます。動作検証等に使用してください。

### standalone/app/lp/src/

アプリケーション本体に関連するファイル群です。

| ディレクトリ | 説明                                                                                         |
| :----------: | :------------------------------------------------------------------------------------------- |
| Controllers  | ルーティングごとにリクエストを受け取り、処理を振り分けるコントローラークラス群です。         |
|  Extensions  | Twigなど外部ライブラリの拡張やカスタム機能を実装するクラスを配置します。                     |
|    Models    | フォームデータやドメインデータなど、アプリケーションで扱うデータ構造（モデル）を定義します。 |
|    Routes    | URLルーティングの定義ファイルを配置します。                                                  |
|   Services   | バリデーションやメール送信など、ビジネスロジックやサービス処理を担うクラス群です。           |
|    Views     | Twigテンプレートなど、画面表示用のビュー（テンプレート）ファイルを配置します。               |

### standalone/app/lp/vite/

Vite開発環境が入っています。

| ディレクトリ | 説明                                                                                     |
| :----------: | :--------------------------------------------------------------------------------------- |
|    public    | 静的ファイル（画像やフォントなど）を配置するディレクトリです。                           |
|     src      | Viteで管理するフロントエンドのソースコード（JS/TS、CSS、コンポーネント等）を配置します。 |
|    tasks     | Viteや開発環境用のビルド・ユーティリティスクリプトを配置します。                         |

## メール機能について補足説明

PHP Mailerを使ってSMTP送信することを想定しています。  
必要とされそうなリッチ機能は現状組み込んでいないので、必要であれば追加開発してください。

### 実装済み

- CSRF対策（Slim-Csrf）
  - bootstrap.phpにて注入しています。
- サーバーサイドバリデーション（Respect）
  - Services/ValidationServiceで実装しています。
- エラーハンドリング（送信エラーが発生した場合にログ出力し、エラー画面を表示）
  - 主にControllers/FormControllerでハンドリングしています。

### 未実装（必要に応じて追加開発が必要）

- リアルタイムバリデーション（FinalFormなど）
- サーバーサイドバリデーションの結果を入力画面の各項目に反映させる
- スパム対策（reCaptchaなど）
