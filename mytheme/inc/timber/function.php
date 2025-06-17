<?php

use Timber\Timber;
use Timber\Post;
use Twig\TwigFunction;
use Timber\URLHelper;

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

/**
 * 相対URLを整形して返す
 *
 * @param string $current_uri 対象のURL
 * @return string 整形された相対URL
 */
function get_rel_uri($current_uri)
{
	$current_uri = URLHelper::get_rel_url($current_uri, true);
	$current_uri = URLHelper::unpreslashit($current_uri);
	return URLHelper::remove_trailing_slash($current_uri);
}

/**
 * 現在のURLを取得（相対パスで）
 *
 * @return string 相対URL
 */
function get_current_url()
{
	$current_url = URLHelper::get_current_url();
	return get_rel_uri($current_url);
}

/**
 * 現在のURLが指定パスで始まっているか判定
 *
 * @param string $path 比較対象のパス
 * @return bool パスが先頭に一致すれば true
 */
function is_current($path)
{
	$current_url = get_current_url();
	return strpos($current_url, $path) === 0;
}

/**
 * h1〜h6タグに自動でidを付与する
 *
 * @param string $html 入力HTML
 * @return string id付きのHTML
 */
function add_ids_to_heading_tags($html)
{
	$doc = new DOMDocument();
	libxml_use_internal_errors(true);
	$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
	libxml_clear_errors();

	$tags = ["h1", "h2", "h3", "h4", "h5", "h6"];
	$idCounter = 1;

	foreach ($tags as $tag) {
		$elements = $doc->getElementsByTagName($tag);
		foreach ($elements as $element) {
			$element->setAttribute("id", "heading-" . $idCounter);
			$idCounter++;
		}
	}

	return $doc->saveHTML();
}

/**
 * HTMLから見出しタグ情報を抽出する
 *
 * @param string $html 入力HTML
 * @return array 抽出されたタグ情報（tag, content, attributes）
 */
function get_heading_tags($html)
{
	$matches = [];
	preg_match_all('/<(h[1-6])([^>]*)>(.*?)<\/\1>/', $html, $matches, PREG_SET_ORDER);

	$hTags = [];
	foreach ($matches as $match) {
		$tag = $match[1];
		$attributesString = $match[2];
		$content = $match[3];

		$attributes = [];
		preg_match_all('/(\w+)=["\']([^"\']*)["\']/', $attributesString, $attrMatches, PREG_SET_ORDER);
		foreach ($attrMatches as $attr) {
			$attributes[$attr[1]] = $attr[2];
		}

		$hTags[] = [
			"tag" => $tag,
			"content" => $content,
			"attributes" => $attributes,
		];
	}

	return $hTags;
}

/**
 * デバッグ出力（HTML表示）
 *
 * @param mixed $obj 表示する変数
 * @param string $label 任意のラベル
 */
function debug($obj, $label = "")
{
	$label = "[Debug] : {$label}";
	$label .= " in ";
	$traces = debug_backtrace();
	$count = 0;
	foreach ($traces as $trace) {
		if (isset($trace["file"], $trace["line"]) && __FILE__ != $trace["file"]) {
			$label .= $trace["file"] . " (" . $trace["line"] . ")";
			if (++$count >= 5) {
				break;
			} else {
				$label .= "<br />";
			}
		}
	}
	echo '<div style="font:11px/1.2 Lucida Grande, Verdana, Geneva, Sans-serif; margin: 1em 0; padding: 0.5em; background:#e9e9e9; border:1px solid #D0D0D0;">';
	if (strlen($label)) {
		echo "<strong>" . $label . "</strong>";
	}
	echo '<pre style="display: block; background:#F4F4F4; border:1px solid #D0D0D0; color: #002166; margin:0.5em 0; padding:1em;">';
	if (is_bool($obj)) {
		echo $obj ? "true" : "false";
	} elseif (is_array($obj) || is_object($obj)) {
		print_r($obj);
	} else {
		echo $obj;
	}
	echo "</pre>";
	echo "</div>";
}

/**
 * Vite開発サーバーとの接続確認
 *
 * @return bool 接続可能なら true、失敗なら false
 */
function check_vite_connection()
{
	if (!isset($_ENV["IS_DEVELOPMENT"]) || !$_ENV["IS_DEVELOPMENT"]) {
		return false;
	}

	$host = "host.docker.internal";
	$port = 3000;
	$connection = @fsockopen($host, $port, $errno, $errstr, 5); // 5秒タイムアウト

	if ($connection) {
		fclose($connection);
		return true;
	} else {
		return false;
	}
}

/**
 * ログファイルに任意のデータを書き出す（明示的にファイル指定）
 *
 * @param mixed $var ログ対象
 * @param string $prefix メッセージの前に付ける文字列
 * @param string $suffix メッセージの末尾に付ける文字列（改行など）
 */
function simple_logger($var, $prefix = "", $suffix = "\n")
{
	error_log(
		$prefix . print_r($var, true) . $suffix,
		3,
		get_template_directory() . "/my-errors.log"
	);
}

/**
 * ACFのカスタムフィールドの値を取得するget_field()をtwig上で使えるようにラップ
 * - https://www.advancedcustomfields.com/resources/get_field/
 *
 * @param string $selector フィールドの名前（フィールドキーまたは名前）。
 * @param int|string|null $id 投稿ID、オプションページの識別子(option)、または null（現在の投稿）。
 * @return mixed フィールドの値。存在しない場合は null または false。
 */
function twig_acf_get_field($selector, $id = null)
{
	return get_field($selector, $id);
}

add_filter("timber/twig", function ($twig) {
	$twig->addFunction(new TwigFunction("breadcrumb", "get_breadcrumb_items"));
	$twig->addFunction(new TwigFunction("get_rel_uri", "get_rel_uri"));
	$twig->addFunction(new TwigFunction("get_current_url", "get_current_url"));
	$twig->addFunction(new TwigFunction("is_current", "is_current"));
	$twig->addFunction(new TwigFunction("add_ids_to_heading_tags", "add_ids_to_heading_tags"));
	$twig->addFunction(new TwigFunction("get_heading_tags", "get_heading_tags"));
	$twig->addFunction(new TwigFunction("debug", "debug"));
	$twig->addFunction(new TwigFunction("check_vite_connection", "check_vite_connection"));
	$twig->addFunction(new TwigFunction("simple_logger", "simple_logger"));
	$twig->addFunction(new TwigFunction("get_field", "twig_acf_get_field"));
	return $twig;
});
