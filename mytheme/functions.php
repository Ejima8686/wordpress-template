<?php

namespace WordPressStarter\Theme;

define("THEME_NAME", basename(__DIR__));

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/inc/vite-assets.php";
require_once __DIR__ . "/inc/blocks.php";
require_once __DIR__ . "/inc/acf-blocks.php";
require_once __DIR__ . "/inc/timber/context.php";
require_once __DIR__ . "/inc/timber/function.php";
require_once __DIR__ . "/inc/posts/mytheme_news.php";

/**
 * ログイン中かつ開発環境の場合、管理バー（Admin Bar）を非表示にする。
 */
if (is_user_logged_in() && is_dev()) {
	add_filter("show_admin_bar", "__return_false");
}

/**
 * WordPressが自動的に出力する `global-styles-inline-css` を読み込みから除外する。
 * - Tailwind CSS や base.css のスタイルを上書きしてしまうため。
 */
add_action("wp_enqueue_scripts", function () {
	wp_dequeue_style("global-styles");
});

if (!isset($content_width)) {
	$content_width = 1280;
}

/**
 * テーマ設定の初期化
 */
add_action("after_setup_theme", function () {
	load_theme_textdomain(THEME_NAME, __DIR__ . "/languages");

	add_theme_support("title-tag");

	add_theme_support("post-thumbnails");

	// register_nav_menus([
	// 	"primary" => "Primary",
	// ]);

	add_theme_support("html5", [
		"comment-form",
		"comment-list",
		"search-form",
		"gallery",
		"caption",
		"style",
		"script",
		"navigation-widgets",
	]);

	add_theme_support("customize-selective-refresh-widgets");

	add_theme_support("editor-styles");
	add_editor_style();

	add_theme_support("align-wide");
	add_theme_support("responsive-embeds");
});
