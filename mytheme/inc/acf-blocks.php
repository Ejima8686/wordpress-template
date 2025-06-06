<?php

use Timber\Timber;

/**
 * ACFブロックの表示用コールバック関数
 * - エディタ上でのプレビュー画像の設定
 * - ビューファイル（twig）のレンダリング
 *
 * @param array  $block      ブロックの設定と属性情報。
 * @param string $block_slug    ブロックの名前（acf/を除く）。
 * @param string $content    ブロックの内容（通常は空文字）。
 * @param bool   $is_preview エディター上のプレビュー表示時は true。
 */
function my_acf_block_render_callback($block, $content, $is_preview)
{
	$block_slug = str_replace("acf/", "", $block["name"]);

	$context = Timber::context();
	foreach (
		[
			"block" => $block,
			"block_slug" => $block_slug,
			"fields" => get_fields(),
			"is_preview" => $is_preview,
		]
		as $key => $value
	) {
		$context[$key] = $value;
	}

	/**
	 * プレビュー画像の設定
	 */
	if (!empty($block["data"]["__is_preview"])) {
		$assets_dir = get_template_directory() . "/assets/blocks-preview/";
		$assets_url = get_template_directory_uri() . "/assets/blocks-preview/";
		$preview_image_name = $block_slug . ".png";

		$preview_image_path = $assets_dir . $preview_image_name;
		$preview_image_url = $assets_url . $preview_image_name;

		if (file_exists($preview_image_path)) {
			echo "<img src='" .
				esc_url($preview_image_url) .
				"' alt='{$block_slug} preview' style='width:100%; height:auto;' />";
			return;
		}
	}

	Timber::render("blocks/" . $block_slug . ".twig", $context);
}

/**
 * 指定された添付画像IDから <source> タグのHTMLを生成
 *
 * @param int         $attachment_id 添付画像（メディア）ID。
 * @param string      $size          出力する画像サイズ。デフォルトは 'thumbnail'。
 * @param bool        $icon          true の場合、添付画像が存在しないときにアイコンを表示。デフォルトは false。
 * @param array|string $attr         追加属性。配列またはクエリ文字列形式で srcset や type などを指定可能。
 *
 * @return string 出力された <source> タグのHTML。画像が存在しない場合は空文字列。
 */
function my_get_attachment_source($attachment_id, $size = "thumbnail", $icon = false, $attr = "")
{
	$html = "";
	$image = wp_get_attachment_image_src($attachment_id, $size, $icon);

	if ($image) {
		list($src, $width, $height) = $image;

		$attachment = get_post($attachment_id);
		$hwstring = image_hwstring($width, $height);

		$attr = wp_parse_args($attr);

		if (empty($attr["srcset"])) {
			$image_meta = wp_get_attachment_metadata($attachment_id);

			if (is_array($image_meta)) {
				$size_array = [absint($width), absint($height)];
				$srcset = wp_calculate_image_srcset($size_array, $src, $image_meta, $attachment_id);
				$sizes = wp_calculate_image_sizes($size_array, $src, $image_meta, $attachment_id);

				if ($srcset && ($sizes || !empty($attr["sizes"]))) {
					$attr["srcset"] = $srcset;

					if (empty($attr["sizes"])) {
						$attr["sizes"] = $sizes;
					}
				}
			}
		}

		$attr = array_map("esc_attr", $attr);
		$html = rtrim("<source $hwstring");

		foreach ($attr as $name => $value) {
			$html .= " $name=" . '"' . $value . '"';
		}

		$html .= " />";
	}

	return $html;
}
