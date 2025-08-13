# standaloneの使い方

---

## 概要

WordPress外にフォームを含むページを設置できる様に standalone という仕組みを用意しています。

立ち上げまでは `standalone/README.md` を参照してください。

---

## リリース時の注意点

`standalone/app/` ディレクトリは サーバーの **非公開領域** に 配置することを想定しています。

この中に `mail-demo` などの、個別アプリディレクトリがあるはずです。

`standalone/public` ディレクトリは サーバーの **公開領域** を想定しています。

この中に `mail-demo` などの、個別アプリディレクトリがあるはずです。

### `mail-demo` を作って、さくらやXServerに配置する場合

`/home/account_name/app/mail-demo/`  に `standalone/app/mail-demo/` を配置します。

※account_nameはアカウントに合わせて変更

`/home/account_name/www/mail-demo/`  もしくは `/home/account_name/public_html/mail-demo/`  に `standalone/public/mail-demo/` を配置します。

※account_nameはアカウントに合わせて変更
