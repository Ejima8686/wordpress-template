<?php
add_action("admin_menu", function () {
	add_menu_page(
		"Dummy Post Generator",
		"ダミー投稿作成",
		"manage_options",
		"dummy-post-generator",
		"render_dummy_post_generator_page"
	);
});

function render_dummy_post_generator_page()
{
	if ($_SERVER["REQUEST_METHOD"] === "POST" && check_admin_referer("generate_dummy_posts")) {
		$errors = [];

		$post_type = sanitize_text_field($_POST["post_type"]);
		$taxonomy = sanitize_text_field($_POST["taxonomy"]);
		$post_count = intval($_POST["post_count"]);
		$raw_lines = preg_split('/[\r\n,]+/', $_POST["taxonomies"]);
		$raw_lines = array_filter(array_map("trim", $raw_lines));
		$taxonomy_data = array_map(function ($line) {
			return explode("|", $line);
		}, $raw_lines);

		if (!post_type_exists($post_type)) {
			$errors[] = "指定された投稿タイプが存在しません。";
		}

		if (!taxonomy_exists($taxonomy)) {
			$errors[] = "指定されたタクソノミーが存在しません。";
		}

		if ($post_count < 1) {
			$errors[] = "記事数は1以上で指定してください。";
		}

		foreach ($taxonomy_data as $row) {
			if (count($row) !== 2 || empty($row[0]) || empty($row[1])) {
				$errors[] =
					"カテゴリの形式が正しくありません。「名前|スラッグ」の形式で1行ずつ入力してください。";
				break;
			}
		}

		if (!empty($errors)) {
			foreach ($errors as $error) {
				echo '<div class="notice notice-error"><p>' . esc_html($error) . "</p></div>";
			}
		} else {
			generate_dummy_posts($post_type, $taxonomy, $post_count, $taxonomy_data);
			echo '<div class="notice notice-success"><p>投稿を生成しました。</p></div>';
		}
	}

	include plugin_dir_path(__FILE__) . "views/form.php";
}
