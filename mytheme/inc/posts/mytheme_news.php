<?php

namespace WordPressStarter\Theme;

add_action("init", function () {
	register_post_type("mytheme_news", [
		"label" => "お知らせ",
		"public" => true,
		"supports" => ["title", "editor", "thumbnail", "revisions", "page-attributes"],
		"has_archive" => true,
		"show_in_rest" => true,
		"rewrite" => ["slug" => "mytheme_news"],
	]);

	register_taxonomy(
		"mytheme_news_category",
		["mytheme_news"],
		[
			"label" => "カテゴリー",
			"has_archive" => true,
			"show_in_rest" => true,
		]
	);
});

add_action("pre_get_posts", function ($query) {
	if (is_admin() || !$query->is_main_query()) {
		return;
	}

	if (is_post_type_archive("mytheme_news") || is_tax("mytheme_news_category")) {
		$query->set("posts_per_page", 6);
	}
});
