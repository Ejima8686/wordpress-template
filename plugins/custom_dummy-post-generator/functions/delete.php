<?php
function dummy_delete_posts_by_type($post_type)
{
	$posts = get_posts([
		"post_type" => $post_type,
		"post_status" => "any",
		"numberposts" => -1,
		"meta_key" => "_created_by_dummy_post_manager",
		"meta_value" => true,
	]);

	foreach ($posts as $post) {
		wp_delete_post($post->ID, true); // 完全削除
	}

	return count($posts);
}

function dummy_delete_terms($taxonomy)
{
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
		return 0;
	}

	$deleted = 0;
	foreach ($terms as $term) {
		$result = wp_delete_term($term->term_id, $taxonomy);
		if (!is_wp_error($result)) {
			$deleted++;
		}
	}

	return $deleted;
}
