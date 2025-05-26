<?php
/*
* Plugin Name: ダミー記事ジェネレーター
* Version: 1.0
* Description: 特定の投稿タイプのダミー記事を生成します。
* Author:  
*/

require_once plugin_dir_path(__FILE__) . 'admin-page.php';
require_once plugin_dir_path(__FILE__) . 'generator.php';

// プラグイン管理画面に設定リンクを追加
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'add_settings_link');

function add_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=dummy-post-generator">設定</a>';
    array_unshift($links, $settings_link);
    return $links;
}
