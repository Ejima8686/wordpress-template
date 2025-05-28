<?php
/*
 * Plugin Name: Dummy Post Manager
 * Version: 1.0
 * Description: 特定の投稿タイプのダミー記事を生成・管理します。
 * Author:
 */

require_once plugin_dir_path(__FILE__) . "admin-page.php";
require_once plugin_dir_path(__FILE__) . "functions/delete.php";
require_once plugin_dir_path(__FILE__) . "functions/generate.php";

// プラグイン管理画面に設定リンクを追加
add_filter("plugin_action_links_" . plugin_basename(__FILE__), "dummy_post_manager_action_links");

function dummy_post_manager_action_links($links)
{
	$url = admin_url("admin.php?page=dummy-post-generator");
	$settings_link = '<a href="' . esc_url($url) . '">' . __("設定", "dummy-post-generator") . "</a>";
	array_unshift($links, $settings_link);
	return $links;
}
