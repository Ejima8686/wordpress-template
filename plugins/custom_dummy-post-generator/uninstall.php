<?php
if (!defined("WP_UNINSTALL_PLUGIN")) {
	die();
}

// 投稿削除
$post_types = get_post_types(["public" => true], "names");
foreach ($post_types as $post_type) {
	$posts = get_posts([
		"post_type" => $post_type,
		"post_status" => "any",
		"numberposts" => -1,
		"meta_key" => "_created_by_dummy_post_manager",
		"meta_value" => true,
	]);

	foreach ($posts as $post) {
		wp_delete_post($post->ID, true);
	}
}

// ターム削除
$taxonomies = get_taxonomies([], "names");
foreach ($taxonomies as $taxonomy) {
	$terms = get_terms([
		"taxonomy" => $taxonomy,
		"hide_empty" => false,
		"meta_query" => [
			[
				"key" => "_created_by_dummy_post_manager",
				"value" => true,
			],
		],
	]);

	if (is_wp_error($terms) || empty($terms)) {
		continue;
	}

	foreach ($terms as $term) {
		wp_delete_term($term->term_id, $taxonomy);
	}
}
