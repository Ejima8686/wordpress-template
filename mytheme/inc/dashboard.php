<?php

/**
 * 管理画面のメニュー・サブメニューを制御
 */
add_action("admin_menu", function () {
	// remove_submenu_page("index.php", "update-core.php"); // ダッシュボード > 更新
	// remove_menu_page("edit.php"); // 投稿メニュー
	// remove_menu_page("edit.php?post_type=page"); // 固定ページメニュー

	// if (current_user_can("administrator")) {
	// } elseif (current_user_can("editor")) {
	//   remove_menu_page("edit.php"); // 投稿
	//   remove_menu_page("edit.php?post_type=page"); // 固定ページ
	//   remove_menu_page("tools.php"); // ツール
	// } else {
	//   remove_menu_page("index.php"); // ダッシュボード
	//   remove_menu_page("upload.php"); // メディア
	//   remove_menu_page("edit-comments.php"); // コメント
	//   remove_menu_page("themes.php"); // 外観
	//   remove_menu_page("plugins.php"); // プラグイン
	//   remove_menu_page("users.php"); // ユーザー
	//   remove_menu_page("tools.php"); // ツール
	//   remove_menu_page("options-general.php"); // 設定

	//   remove_submenu_page("index.php", "index.php"); // ダッシュボード / ホーム

	//   remove_submenu_page("edit.php", "edit.php"); // 投稿 / 投稿一覧
	//   remove_submenu_page("edit.php", "post-new.php"); // 投稿 / 新規追加
	//   remove_submenu_page("edit.php", "edit-tags.php?taxonomy=category"); // 投稿 / カテゴリー
	//   remove_submenu_page("edit.php", "edit-tags.php?taxonomy=post_tag"); // 投稿 / タグ

	//   remove_submenu_page("upload.php", "upload.php"); // メディア / ライブラリ
	//   remove_submenu_page("upload.php", "media-new.php"); // メディア / 新規追加

	//   remove_submenu_page("edit.php?post_type=page", "edit.php?post_type=page"); // 固定 / 一覧
	//   remove_submenu_page("edit.php?post_type=page", "post-new.php?post_type=page"); // 固定 / 新規追加

	//   remove_submenu_page("themes.php", "themes.php"); // 外観 / テーマ
	//   remove_submenu_page("themes.php", "customize.php?return=" . rawurlencode($_SERVER["REQUEST_URI"])); // 外観 / カスタマイズ
	//   remove_submenu_page("themes.php", "nav-menus.php"); // 外観 / メニュー
	//   remove_submenu_page("themes.php", "widgets.php"); // 外観 / ウィジェット
	//   remove_submenu_page("themes.php", "theme-editor.php"); // 外観 / テーマエディタ

	//   remove_submenu_page("plugins.php", "plugins.php"); // プラグイン / 一覧
	//   remove_submenu_page("plugins.php", "plugin-install.php"); // プラグイン / 新規追加
	//   remove_submenu_page("plugins.php", "plugin-editor.php"); // プラグイン / エディタ

	//   remove_submenu_page("users.php", "users.php"); // ユーザー / 一覧
	//   remove_submenu_page("users.php", "user-new.php"); // ユーザー / 新規追加
	//   remove_submenu_page("users.php", "profile.php"); // ユーザー / プロフィール

	//   remove_submenu_page("tools.php", "tools.php"); // ツール / 一覧
	//   remove_submenu_page("tools.php", "import.php"); // ツール / インポート
	//   remove_submenu_page("tools.php", "export.php"); // ツール / エクスポート
	//   remove_submenu_page("tools.php", "site-health.php"); // ツール / サイトヘルス
	//   remove_submenu_page("tools.php", "export_personal_data"); // ツール / 個人データ出力
	//   remove_submenu_page("tools.php", "remove_personal_data"); // ツール / 個人データ消去

	//   remove_submenu_page("options-general.php", "options-general.php"); // 設定 / 一般
	//   remove_submenu_page("options-general.php", "options-writing.php"); // 設定 / 投稿設定
	//   remove_submenu_page("options-general.php", "options-reading.php"); // 設定 / 表示設定
	//   remove_submenu_page("options-general.php", "options-discussion.php"); // 設定 / ディスカッション
	//   remove_submenu_page("options-general.php", "options-media.php"); // 設定 / メディア
	//   remove_submenu_page("options-general.php", "options-permalink.php"); // 設定 / パーマリンク
	//   remove_submenu_page("options-general.php", "privacy.php"); // 設定 / プライバシー
	// }
});

/**
 * 更新通知・メンテナンス通知を削除
 */
add_action("admin_init", function () {
	// remove_action('admin_notices', 'maintenance_nag', 10); // メンテナンスモード通知
	// remove_action("admin_notices", "update_nag", 3); // バージョンアップ通知
});

/**
 * ダッシュボードの各パネル（ウィジェット）を削除
 */
add_action("wp_dashboard_setup", function () {
	// remove_meta_box("dashboard_site_health", "dashboard", "normal"); // サイトヘルス
	// remove_meta_box("dashboard_right_now", "dashboard", "normal"); // 概要
	// remove_meta_box("dashboard_activity", "dashboard", "normal"); // アクティビティ
	// remove_meta_box("dashboard_quick_press", "dashboard", "side"); // クイックドラフト
	// remove_meta_box("dashboard_primary", "dashboard", "side"); // WordPressニュース
	// remove_action("welcome_panel", "wp_welcome_panel"); // ようこそパネル
});
