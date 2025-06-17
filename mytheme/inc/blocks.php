<?php

namespace WordPressStarter\Theme;

use Timber\Timber;

/**
 * 開発環境のときのみ、
 * ブロックエディタ上でホットリロードが有効になり、スタイル、スクリプトが即時反映されるようになります。
 *	 - ※これを有効にする場合、@vite/clientを参照するため、localhostが立ち上がってないとエディタが立ち上がりません。
 * @hook admin_enqueue_scripts
 */
add_action("admin_enqueue_scripts", function () {
	$IS_VITE_RUNNING = check_vite_connection();
	global $pagenow;
	if (
		($_ENV["IS_DEVELOPMENT"] || $IS_VITE_RUNNING) &&
		($pagenow === "post.php" || $pagenow === "post-new.php")
	) {
		echo '<script type="module" src="http://localhost:3000/@vite/client"></script>';
		echo '<script type="module" src="http://localhost:3000/source/index.ts"></script>';
	}
});

/**
 * ブロックエディタに対し、ビルドされたCSSを適用します。
 *
 * @hook enqueue_block_editor_assets
 */
add_action("enqueue_block_editor_assets", function () {
	$manifest_path = dirname(__DIR__) . "/build/.vite/manifest.json";

	if (!file_exists($manifest_path)) {
		return;
	}

	$manifest = json_decode(file_get_contents($manifest_path), true);
	$entry = $manifest["source/index.ts"] ?? null;

	if (!$entry || empty($entry["css"])) {
		return;
	}

	$build_path = get_template_directory_uri() . "/build/";

	foreach ($entry["css"] as $css) {
		wp_enqueue_style("block-editor-styles", $build_path . $css, [], "1.0", "all");
	}
});

/**
 * カスタムブロックのレンダリング
 */
add_action(
	"init",
	function () {
		foreach (glob(dirname(__DIR__) . "/blocks/*", GLOB_ONLYDIR) as $dir) {
			register_block_type($dir);
		}
	},
	5
);

/**
 * 使いたいブロックのホワイトリスト
 */
add_filter(
	"allowed_block_types_all",
	function ($allowed_block_types, $block_editor_context) {
		$block_types = [
			"core/paragraph",
			"core/image",
			"core/heading",
			"acf/testimonial",
			// ここにブロックを追加
		];
		return $block_types;
	},
	10,
	2
);
