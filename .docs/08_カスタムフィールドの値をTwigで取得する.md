# カスタムフィールドの値をTwigで取得する

関連記事: [カスタムブロックの作り方](09_カスタムブロックの作り方.md)<br>[オプションページの作り方](11_オプションページの作り方.md)<br>
親記事: [ACFの使い方](06_ACFの使い方.md)

---

## 目次

- [準備](#準備)
- [Twigでの使い方](#twigでの使い方)
  - [テキストフィールド](#テキストフィールド)
  - [繰り返しフィールド](#繰り返しフィールド)
  - [画像フィールド](#画像フィールド)
  - [ギャラリーフィールド](#ギャラリーフィールド)
  - [真偽値フィールド](#真偽値フィールド)
  - [グループフィールド](#グループフィールド)
- [オプションページの値を取得](#オプションページの値を取得)
  - [**get_fields('options')の注意点について**](#get_fieldsoptionsの注意点について)

---

## 準備

ACFで作られたカスタムフィールドの取得には、**[get_field()](https://www.advancedcustomfields.com/resources/get_field/)**が使えます。

`mytheme/inc/timber/function.php` にて、Twigファイル上で`get_field()`が使えるように仕込んであります。

```php
// 関数をラップ
// 引数にはフィールド名と投稿IDを渡します。
function twig_acf_get_field($selector, $id = null)
{
	return get_field($selector, $id);
}

// twigで使えるように登録
add_filter("timber/twig", function ($twig) {
	$twig->addFunction(new TwigFunction("get_field", "acf_get_field"));
	return $twig;
});
```

## Twigでの使い方

Twigでは以下のように使用します。

固定ページや詳細ページの場合、get_field()の引数にIDは渡さなくても大丈夫です。

> ⚠️オプションページの内容を取得する場合は、`get_field('loop', 'option')`のように、第２引数に`option`と入力してください。
> 
> → https://www.advancedcustomfields.com/resources/get-values-from-an-options-page/

### テキストフィールド

![テキストフィールド設定](カスタムフィールドの値をTwigで取得する/スクリーンショット_2025-06-17_16.28.06.png)

![テキストフィールド表示](カスタムフィールドの値をTwigで取得する/スクリーンショット_2025-06-17_16.25.48.png)

```php
<div class="py-5">
	<h2 class="text-2xl mb-8 text-red-800 font-bold">テキストフィールド</h2>
	{{ get_field('text') }}
</div>
```

---

### 繰り返しフィールド

![繰り返しフィールド設定](カスタムフィールドの値をTwigで取得する/スクリーンショット_2025-06-17_16.28.33.png)

![繰り返しフィールド表示](カスタムフィールドの値をTwigで取得する/スクリーンショット_2025-06-17_16.25.57.png)

```php
<div class="py-5">
	<h2 class="text-2xl mb-8 text-red-800 font-bold">繰り返しフィールド</h2>
	{% for item in get_field('loop') %}
		<div class="mb-2 flex gap-8 items-center">
			{{ item.text }}
			<img src="{{ item.image }}" alt="" class="w-[200px]">
		</div>
	{% endfor %}
</div>
```

---

### 画像フィールド

![画像フィールド設定](カスタムフィールドの値をTwigで取得する/スクリーンショット_2025-06-17_16.28.52.png)

![画像フィールド表示](カスタムフィールドの値をTwigで取得する/スクリーンショット_2025-06-17_16.26.05.png)

```php
<div class="py-5">
	<h2 class="text-2xl mb-8 text-red-800 font-bold">画像フィールド</h2>
	<img src={{ get_field('image') }} alt="" class="w-[200px]">
</div>
```

---

### ギャラリーフィールド

![ギャラリーフィールド設定](カスタムフィールドの値をTwigで取得する/スクリーンショット_2025-06-17_16.29.06.png)

![ギャラリーフィールド表示](カスタムフィールドの値をTwigで取得する/スクリーンショット_2025-06-17_16.26.20.png)

```php
	<div class="py-5">
		<h2 class="text-2xl mb-8 text-red-800 font-bold">ギャラリーフィールド</h2>
		<div class="grid grid-cols-4 gap-3">
			{% for item in get_field('gallary') %}
				<div class="mb-2 flex gap-4 flex-col items-center">
					<img src="{{ item.url }}" alt="" class="w-[200px]">
					<p class="text-[12px]">{{ item.title }}</p>
				</div>
			{% endfor %}
		</div>
	</div>
```

---

### 真偽値フィールド

![真偽値フィールド設定](カスタムフィールドの値をTwigで取得する/スクリーンショット_2025-06-17_16.29.20.png)

![真偽値フィールド表示](カスタムフィールドの値をTwigで取得する/スクリーンショット_2025-06-17_16.26.31.png)

```php
<div class="py-5">
	<h2 class="text-2xl mb-8 text-red-800 font-bold">真偽値フィールド</h2>
	{% if get_field('boolean_true') %}
		true!
	{% endif %}
</div>
```

---

### グループフィールド

![グループフィールド設定](カスタムフィールドの値をTwigで取得する/スクリーンショット_2025-06-17_16.29.32.png)

![グループフィールド表示](カスタムフィールドの値をTwigで取得する/スクリーンショット_2025-06-17_16.26.38.png)

```php
<div class="py-5">
	<h2 class="text-2xl mb-8 text-red-800 font-bold">グループフィールド</h2>
		{% set group = get_field('group', post.id) %}
	<div class="grid grid-cols-4 gap-3">
		<div class="mb-2 flex gap-4 flex-col items-center">
			<img src="{{ group.image }}" alt="" class="w-[200px]">
			<p class="text-[12px]">{{ group.text }}</p>
		</div>
	</div>
</div>
```

## オプションページの値を取得

`get_fields( 'options' );`で、オプションページのフィールドの値にアクセスできます。

https://www.advancedcustomfields.com/resources/get_fields/#get-values-from-a-specific-post

`mytheme/inc/timber/context.php` にて以下のような処理をしています。

```php
<?php

use Timber\Timber;

add_filter("timber/context", function ($context) {
	$context["options"] = get_fields("options");
	$context["about_post"] = Timber::get_post([
		"name" => "about",
		"post_type" => "page",
	]);

	return $context;
});
```

これにより、Twig上では`options`で値にアクセスできるようにしています。
例えば、オプションページの`options_image`を取得する場合は以下のようにします。

![オプションページの値取得](カスタムフィールドの値をTwigで取得する/スクリーンショット_2025-06-17_18.48.56.png)

```php
	<pre>
		{{ dump(options.options_image) }}
	</pre>
```

### **get_fields('options')の注意点について**

> `get_fields('options') `を使うと、**すべてのオプションページのフィールドが混ざって返ってきます**。
> 
> たとえば、以下のような構成があったとします：
> •	メインオプションページ：About
> •	サブオプションページ：Home、Company
> 
> メイン、サブでページが分かれているのですが、これらのページに登録されたフィールドは、すべて `post_id = 'option'` に保存されており、実際には区別されていません。
> 
> そのため、**異なるオプションページで同じフィールド名を使っていると、正しく値を取得できません。**
> 
> 
> オプションページのフィールドには、**ユニークなフィールド名** をつけることをおすすめします。
> 例）　options_[ オプションページ名 ]_text　など…
