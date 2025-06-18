<?php

namespace WordPressStarter\Theme;

/**
 * コメントページを削除
 */
add_action("admin_menu", function () {
	remove_menu_page("edit-comments.php");
});

/**
 * 管理画面にadmin.cssを適用
 */
add_action("admin_enqueue_scripts", function () {
	wp_enqueue_style(THEME_NAME . "-admin", get_template_directory_uri() . "/admin.css");
});

/**
 * 画像アップロード時に自動縮小される最大幅を4Kの幅で指定
 */
add_filter("big_image_size_threshold", function () {
	return 2 * 1920;
});

/**
 * ACFのWYSIWYGフィールドにカスタムツールバーを追加
 *  - https://www.advancedcustomfields.com/resources/customize-the-wysiwyg-toolbars/#usage
 */
add_filter("acf/fields/wysiwyg/toolbars", function ($toolbars) {
	/*
		echo '<pre>';
			print_r($toolbars);
		echo '</pre>';
		die;
	*/

	$toolbars["Very Simple"] = [];
	$toolbars["Very Simple"][1] = ["bold", "italic", "underline"]; // WYSIWYG 1行目
	$toolbars['Very Simple' ][2] = ["link", "bullist", "numlist"]; // WYSIWYG 2行目

	/*
	"formatselect",
	"bold",
	"italic",
	"bullist",
	"numlist",
	"blockquote",
	"alignleft",
	"aligncenter",
	"alignright",
	"link",
	"wp_more",
	"spellchecker",
	"fullscreen",
	"wp_adv",
	*/

	return $toolbars;
});

/**
 * ワードプレスにアップロードできるファイルタイプを増やす
 */
add_filter("upload_mimes", function ($file_types) {
	$file_types["svg"] = "image/svg+xml";
	return $file_types;
});
