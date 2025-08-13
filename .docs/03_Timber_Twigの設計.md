
# Timber / Twigの設計

関連記事: [Twigファイルの作り方](07_Twigファイルの作り方.md)

---

## 目次

- [✒️ ビュー層の基本構造](#️-ビュー層の基本構造)
- [🧠 ロジック層の構造](#-ロジック層の構造)
- [📃 補遺](#-補遺)

---

> 当開発環境では、**Twig / Timber** を採用しています。
> 
> https://timber.github.io/docs/v2/
> https://twig.symfony.com/
> 
> 基本的な使い方は、Tmberのチュートリアルを参照ください。
> https://timber.github.io/docs/v2/getting-started/introduction/

## ✒️ ビュー層の基本構造

ビュー層のコード（HTML部分）は`mytheme/views` にて管理します。

https://github.com/timber/starter-theme　構造はTimberのスターターテーマを参考にしています。

```
view/
├── layouts  ... 共通レイアウトです。templatesのファイルでextendsされます。
├── partials　... 再利用可能なパーツ
└── templates　... ページ単位のテンプレート
```

`templates` のファイルをphpでレンダリングして、[wordpressのテンプレート階層](https://ja.wordpress.org/team/handbook/theme-development/basics/template-hierarchy/)に伴ったビューを表示します。

## 🧠 ロジック層の構造

データの操作、PHPの関数はすべて`mytheme/inc` に格納します。

```
inc/
├── posts/
│   └── mytheme_news.php
├── timber/
│   ├── context.php
│   └── function.php
└── vite-assets.php
```

| パス | 役割・説明 | 簡単に言えば |
| --- | --- | --- |
| inc/posts/ … php | register_post_type() を使って、カスタム投稿を追加します。 | カスタム投稿タイプを作る |
| inc/timber/context.php | timber/context フィルターで、Twig に渡す変数（context）を拡張します。 | Twig に渡すデータを追加する |
| inc/timber/function.php | $twig->addFunction により、PHP の関数を Twig で使えるようにします。 | Twig で使える関数を登録する |
| inc/vite-assets.php | Vite でビルドしたJS/CSSを、開発・本番で出し分ける処理をします。 | CSSやJSを環境によって切り替える |

などなど….

各ファイルを機能させるには、`functions.php`にインクルードする必要があります。

```php
<?php

namespace WordPressStarter\Theme;

...省略

require_once __DIR__ . "/inc/vite-assets.php";
require_once __DIR__ . "/inc/timber/context.php";
require_once __DIR__ . "/inc/timber/function.php";
require_once __DIR__ . "/inc/posts/mytheme_news.php";
```

## 📃 補遺

> 当初、テンプレート開発の方針としては、テンプレートエンジンを用いないことになっていました。
> パッケージやライブラリへの依存による複雑化を防ぎつつ、テンプレートエンジンの学習コストを削減できると考えたためです。
> 
> しかし、**PHPに書いたHTMLは、一般的なコードフォーマッタや拡張機能が正しく適用されない**ことが判明しました。
> （厳密には、PHPのフォーマッタがPHPロジック内部に埋め込まれたHTMLしか対象にしないことが問題でした。）
> 
> この「フォーマットできない」という問題は、**特に社外のエンジニアとコードを共有・レビューする際に大きな支障**となり、**開発効率やコード品質に悪影響を及ぼす**リスクがあります。
> 加えて、PHPテンプレートではロジックとビューが同一ファイルに混在しやすく、**可読性が著しく低下すると考えました**。
> 
> 以上の検討を経て、最終的に
> 自動フォーマットが可能で、ロジック層とビュー層を分離できるテンプレートエンジン「Twig」を導入し、
> それをWordPressで効率的に活用するために「Timber」を採用する方針に転換しました。
> 
> ちなみにBladeを採用する案もありましたが、弊社はLaravel文化圏外であることと、依存性の重さを考慮し、Twig / Tmberの採用に至りました。
