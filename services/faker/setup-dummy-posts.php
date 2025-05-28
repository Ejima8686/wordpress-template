<?php
require_once __DIR__ . "/helpers.php";
require_once __DIR__ . "/delete_posts_data.php";
require_once __DIR__ . "/vendor/autoload.php";
$faker_ja = Faker\Factory::create("ja_JP");

/**
 * ランダムな記事を生成します。
 * ※ 最初にすべての記事を削除します。
 * 以下を設定してください。
 * - POST_TYPE　... 記事を生成するカスタム投稿
 * - POST_TAXONOMY　... カスタム投稿のタクソノミー
 * - POST_COUNT　... 生成する投稿数
 * - TAXONOMIES　... カスタム投稿のタクソノミー。["name", "slug"]
 * - IMAGE_URLS　... アイキャッチに利用する画像URL
 */

define("POST_TYPE", "mytheme_news");
define("POST_TAXONOMY", "mytheme_news_category");

define("POST_COUNT", 20);

define("TAXONOMIES", [
	["セクション #1", "section-1"],
	["セクション #2", "section-2"],
	["セクション #3", "section-3"],
	["セクション #4", "section-4"],
]);

/** https://picsum.photos/#:~:text=300/%3Fblur%3D2-,Advanced%20Usage,-You%20may%20combinedefine */
define(
	"IMAGE_URLS",
	array_map(function () {
		return "https://picsum.photos/800/600.jpg?random=" . uniqid();
	}, range(1, 5))
);

$post_taxonomy = POST_TAXONOMY;
$taxonomies = TAXONOMIES;
$term_ids = [];

foreach ($taxonomies as $term) {
	$term_id = _wp("term create '$post_taxonomy' '$term[0]' --slug='$term[1]' --porcelain");
	if ($term_id) {
		$term_ids[] = trim($term_id);
	}
}

$image_ids = [];

foreach (IMAGE_URLS as $image_url) {
	$image_id = _wp("media import '$image_url' --porcelain");
	if ($image_id) {
		$image_ids[] = trim($image_id);
	}
}

foreach (range(0, POST_COUNT - 1) as $index) {
	$title = "#$index. " . $faker_ja->realText(random_int(50, 150));

	$num_categories = random_int(1, min(3, count($term_ids)));
	$selected_terms = (array) array_rand(array_flip($term_ids), $num_categories);

	$image_id = !empty($image_ids) ? $image_ids[array_rand($image_ids)] : "";

	$post_content = generate_random_content();

	$id = _wp(
		"post create --post_type='" .
			POST_TYPE .
			"' --post_title='$title' --post_content='$post_content' --post_status=publish --porcelain"
	);

	if ($id) {
		if ($image_id) {
			_wp("post meta add $id _thumbnail_id $image_id");
		}

		foreach ($selected_terms as $term_id) {
			_wp("post term add $id $post_taxonomy $term_id --by=id");
		}

		_wp("post update $id --post_name=$id");
	}
}

function generate_random_content()
{
	global $faker_ja;

	$paragraphs = [];

	$num_paragraphs = random_int(3, 5);
	for ($i = 0; $i < $num_paragraphs; $i++) {
		$paragraphs[] =
			"<!-- wp:paragraph --> <p>" .
			$faker_ja->realText(random_int(100, 300)) .
			"</p><!-- /wp:paragraph -->";
	}

	$paragraphs[] = "<p><a href='https://example.com' target='_blank'>詳しくはこちら</a></p>";

	return implode("\n", $paragraphs);
}
