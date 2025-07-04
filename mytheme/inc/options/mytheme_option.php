<?php

namespace WordPressStarter\Theme;

/**
 * ACF オプションページのセットアップ
 *
 * -「サイト内CMS」という親メニューを作成
 * - Home、Companyのサブメニューを追加
 */
add_action("init", function () {
	if (!function_exists("acf_add_options_page")) {
		return;
	}

	$parent_slug = "page-settings";
	acf_add_options_page([
		"page_title" => "サイト設定",
		"menu_title" => "サイト内CMS",
		"menu_slug" => $parent_slug,
		"position" => "99",
		"capability" => "edit_posts",
		"redirect" => true,
	]);

	$sub_pages = [
		["page_title" => "Home", "menu_title" => "Home", "menu_slug" => "home_options"],
		["page_title" => "Company", "menu_title" => "Company", "menu_slug" => "company_options"],
	];

	foreach ($sub_pages as $page) {
		acf_add_options_sub_page(
			array_merge($page, [
				"parent_slug" => $parent_slug,
			])
		);
	}
});
