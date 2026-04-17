# ACFのカスタムブロックの設計

関連記事: [カスタムブロックの作り方](../09_カスタムブロックの作り方/README.md)

---

## 目次

- [仕組み](#仕組み)
  - [1. scaffdogを利用したカスタムブロックの構成ファイル生成](#1-scaffdogを利用したカスタムブロックの構成ファイル生成)
  - [2.ブロックの登録処理](#2ブロックの登録処理)
  - [3.ビューファイルのレンダリング](#3ビューファイルのレンダリング)
  - [4.プレビュー画像の登録](#4プレビュー画像の登録)
- [📃 補遺](#-補遺)

---

> [!WARNING]
> 当開発環境では、**コマンド一つでカスタムブロックを生成できる機能**を実装しました。
> この機能には **ACF PRO** が必要です。
>
> 参照：
> https://www.advancedcustomfields.com/resources/create-your-first-acf-block/

## 仕組み

コマンドが実行されてからブロックが作成されるまでのフローです。

### 1. scaffdogを利用したカスタムブロックの構成ファイル生成

https://scaff.dog/

```shell
npx scaffdog generate acf-block
```

これを実行すると、対話形式でのブロックの登録が始ります。全て設定し終えると、`.scaffdog/acf-block.md` で設定されたファイルが生成されます。

3つの関連ファイルが生成され、結果的に以下のような構造になります。

```
mytheme/
├── acf-json/
│   └── group_custom_[ブロック名].json ← ACFのフィールド定義
├── blocks/
│   └── [ブロック名]/
│       └── block.json　← ブロックの定義
└── view/
    └── blocks/
        └── [ブロック名].twig　← ビュー用のテンプレート
```

### 2.ブロックの登録処理

`mytheme/inc/blocks.php` の`register_block_type()` にて、`mytheme/blocks/[ブロック名]/block.json` で定義されたブロックをACFに登録します。

```php
/**
 * カスタムブロックのレンダリング
 */
add_action(
	"init",
	function () {
		foreach (glob(dirname(__DIR__) . "/blocks/*", GLOB_ONLYDIR) as $dir) {
			register_block_type($dir);
		}
	},
	5
);
```

管理画面からACFを見てみると、「同期」のところに作ったブロックが追加されているはずです。

ただしこれだけでは、編集画面でブロックを配置してもビューが渡されてないので、サイトには何も表示されません。

### 3.ビューファイルのレンダリング

`block.json` では、`renderCallback`にて`my_acf_block_render_callback()`をコールバック関数として指定しています。

```json
{
	"name": "acf/[ブロック名]",

	--- 省略 ---

	"attributes": {},
	"acf": {
		"mode": "edit",
		"renderCallback": "my_acf_block_render_callback"
	},
	"example": {
		"attributes": {
			"mode": "preview",
			"data": {
				"__is_preview": true
			}
		}
	}
}
```

`my_acf_block_render_callback()` は`mytheme/inc/acf-blocks.php`に定義してあります。

やっていることの詳細はコメントの通りです。

この関数によってビューファイル(Twig)がブロックに渡され、サイト上で見た目を確認できるようになります。

```php
<?php

use Timber\Timber;

/**
 * ACFブロックの表示用コールバック関数
 * - エディタ上でのプレビュー画像の設定
 * - ビューファイル（twig）のレンダリング
 *
 * @param array  $block      ブロックの設定と属性情報。
 * @param string $block_slug    ブロックの名前（acf/を除く）。
 * @param string $content    ブロックの内容（通常は空文字）。
 * @param bool   $is_preview エディター上のプレビュー表示時は true。
 */
function my_acf_block_render_callback($block, $content, $is_preview)
{
	$block_slug = str_replace("acf/", "", $block["name"]);

	$context = Timber::context();
	foreach (
		[
			"block" => $block,
			"block_slug" => $block_slug,
			"fields" => get_fields(),
			"is_preview" => $is_preview,
		]
		as $key => $value
	) {
		$context[$key] = $value;
	}

	/**
	 * プレビュー画像の設定
	 */
	　-- - 省略-- - Timber::render("blocks/" . $block_slug . ".twig", $context);
}
```

### 4.プレビュー画像の登録

プレビュー画像はエディタのブロック選択時に表示される画像です。

![プレビュー画像の例](https://deep-space.blue/main/wp-content/uploads/2023/08/image-28.png)

これをブロック毎に登録する処理を、`acf-blocks.php` に定義しています。

```php
/**
 * プレビュー画像の設定
 */
if (!empty($block["data"]["__is_preview"])) {
	$assets_dir = get_template_directory() . "/assets/blocks-preview/";
	$assets_url = get_template_directory_uri() . "/assets/blocks-preview/";
	$preview_image_name = $block_slug . ".png";

	$preview_image_path = $assets_dir . $preview_image_name;
	$preview_image_url = $assets_url . $preview_image_name;

	if (file_exists($preview_image_path)) {
		echo "<img src='" .
			esc_url($preview_image_url) .
			"' alt='{$block_slug} preview' style='width:100%; height:auto;' />";
		return;
	}
}
```

`mytheme/assets/blocks-preview/[ブロック名].png` を置いておけば、それがブロックのプレビュー画像として登録されます。

また、プレビュー画像の表示には、block.jsonで以下のオプションを指定していることが必須です。

```php
	"example": {
		"attributes": {
			"mode": "preview",
			"data": {
				"__is_preview": true
			}
		}
	}
```

## 📃 補遺

> 案件で、詳細ページにカスタムブロックを追加する要件は多いです。
>
> ACFのGUI操作でブロックを作ってももちろんいいのですが、
> Twigテンプレートや `block.json` の構造定義をコード管理することで、
> Gitでのバージョン管理やレビューが容易になり、**保守・デバッグ性が向上**すると判断しました。
>
> **幸い、Gutenberg に対応したカスタムブロックを作成する方法についてのドキュメント**が公式にあったので、これに準えた機能を作ることにしました。
>
> https://www.advancedcustomfields.com/resources/create-your-first-acf-block/
