<?php
require_once __DIR__ . "/../vendor/autoload.php";

function generate_dummy_posts($post_type, $taxonomy, $post_count = 10, $taxonomy_data = [])
{
	$faker = Faker\Factory::create("ja_JP");

	// カテゴリー作成
	$term_ids = [];
	$term_messages = [];

	foreach ($taxonomy_data as [$name, $slug]) {
		$slug = sanitize_title($slug);

		$term = get_term_by("slug", $slug, $taxonomy);
		if (!$term) {
			$term_id = wp_insert_term($name, $taxonomy, ["slug" => $slug]);
			if (!is_wp_error($term_id)) {
				add_term_meta($term_id["term_id"], "_created_by_dummy_post_manager", true, true);
				$term_ids[] = $term_id["term_id"];
				$term_messages[] = "✅ 「{$name}」を追加しました（スラッグ: {$slug}）";
			} else {
				$term_messages[] =
					"⚠️ 「{$name}」の追加に失敗しました（" . $term_id->get_error_message() . "）";
			}
		} else {
			$term_ids[] = $term->term_id;
			$term_messages[] = "ℹ️ 「{$name}」は既に存在しています（スラッグ: {$slug}）";
		}
	}

	// アイキャッチ画像作成
	$image_ids = [];
	$upload_dir = wp_upload_dir();

	foreach (range(1, 5) as $i) {
		$filename = "sample_{$i}.jpg";
		$plugin_image_path = plugin_dir_path(__FILE__) . "../assets/{$filename}";
		$target_path = $upload_dir["path"] . "/" . $filename;

		$existing = get_posts([
			"post_type" => "attachment",
			"post_status" => "inherit",
			"posts_per_page" => 1,
			"title" => sanitize_file_name($filename),
		]);

		if (!empty($existing)) {
			$image_ids[] = $existing[0]->ID;
			continue;
		}

		// コピー元ファイルがなければスキップ
		if (!file_exists($plugin_image_path)) {
			error_log("❌ Not found: $plugin_image_path");
			continue;
		}

		if (!file_exists($target_path)) {
			copy($plugin_image_path, $target_path);
		}

		$filetype = wp_check_filetype($filename);

		$attachment = [
			"post_mime_type" => $filetype["type"],
			"post_title" => sanitize_file_name($filename),
			"post_content" => "",
			"post_status" => "inherit",
		];

		$attach_id = wp_insert_attachment($attachment, $target_path);

		require_once ABSPATH . "wp-admin/includes/image.php";
		$attach_data = wp_generate_attachment_metadata($attach_id, $target_path);
		wp_update_attachment_metadata($attach_id, $attach_data);

		$image_ids[] = $attach_id;
	}

	// 投稿作成
	for ($i = 1; $i <= $post_count; $i++) {
		$title = "#{$i}. " . $faker->realText(random_int(50, 150));

		$post_id = wp_insert_post([
			"post_type" => $post_type,
			"post_title" => $title,
			"post_content" => generate_dummy_content($faker),
			"post_status" => "publish",
		]);

		if (is_wp_error($post_id)) {
			continue;
		}

		update_post_meta($post_id, "_created_by_dummy_post_manager", true);

		if (!empty($image_ids)) {
			set_post_thumbnail($post_id, $image_ids[array_rand($image_ids)]);
		}

		if (!empty($term_ids)) {
			$selected = array_rand(array_flip($term_ids), random_int(1, min(3, count($term_ids))));
			wp_set_object_terms($post_id, (array) $selected, $taxonomy);
		}
	}

	// カテゴリー作成の結果表示
	foreach ($term_messages as $msg) {
		echo '<div class="notice notice-info"><p>' . esc_html($msg) . "</p></div>";
	}
}

function generate_dummy_content($faker)
{
	$paragraphs = [];
	$num_paragraphs = random_int(3, 5);
	for ($i = 0; $i < $num_paragraphs; $i++) {
		$paragraphs[] =
			"<!-- wp:paragraph --><p>" .
			$faker->realText(random_int(100, 300)) .
			"</p><!-- /wp:paragraph -->";
	}
	return implode("\n", $paragraphs);
}
