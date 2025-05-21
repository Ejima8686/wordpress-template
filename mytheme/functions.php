<?php

namespace WordPressStarter\Theme;

define("THEME_NAME", basename(__DIR__));

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/inc/vite-assets.php";

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
