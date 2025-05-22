<?php

use Timber\Timber;
use Timber\Post;
use Twig\TwigFunction;

/**
 * 指定された投稿IDを基点に、パンくずリストの項目を取得します。
 *
 * @param int $post_id 現在の投稿ID。
 * @return array $breadcrumbs パンくずリストの項目を格納した配列。
 */
function get_breadcrumb_items($post_id)
{
	$breadcrumbs = [];
	$current_post = get_post($post_id);

	while ($current_post && $current_post->post_parent) {
		$parent = get_post($current_post->post_parent);
		if ($parent) {
			array_unshift($breadcrumbs, [
				"name" => $parent->post_title,
				"link" => get_permalink($parent->ID),
				"slug" => $parent->post_name,
			]);
			$current_post = $parent;
		} else {
			break;
		}
	}

	if (!empty($breadcrumbs) && $breadcrumbs[0]["slug"] === "service") {
		array_shift($breadcrumbs);
	}

	$breadcrumbs[] = [
		"name" => get_post($post_id)->post_title,
	];

	return $breadcrumbs;
}

add_filter("timber/twig", function ($twig) {
	// $twig->addFunction(new TwigFunction('foo', 'foo_function'));
	$twig->addFunction(new TwigFunction("breadcrumb", "get_breadcrumb_items"));
	return $twig;
});
