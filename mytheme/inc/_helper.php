<?php
use Timber\Timber;

/**
 * Timberテンプレートを出力（ローカル開発環境用処理付き）
 *
 * @param array|string $templates テンプレート名 or 配列
 * @param array $context コンテキスト（変数）
 */
function render_timber_templates($templates, $context)
{
	$IS_DEVELOPMENT = $context["IS_DEVELOPMENT"];
	$render = Timber::compile($templates, $context);

	if (!isset($_SERVER["HTTP_HOST"]) || (!is_admin() && $IS_DEVELOPMENT)) {
		$hostname = $_SERVER["HTTP_HOST"];
		if ($IS_DEVELOPMENT && str_contains($hostname, "localhost")) {
			$hostname = parse_url($hostname, PHP_URL_HOST);
		}
		if (strpos($hostname, ":") !== false) {
			list($hostname, $port) = explode(":", $hostname, 2);
		}
		echo str_replace("localhost", $hostname, $render);
	} else {
		echo $render;
	}
}

/**
 * デバッグ用ログ出力（開発環境のみ）
 *
 * @param mixed $var ログ対象
 * @return bool 出力成功したかどうか
 */
function logger($var)
{
	if (!$_ENV["IS_DEVELOPMENT"]) {
		return false;
	}
	$traces = debug_backtrace();
	$count = 0;
	$label = "";
	foreach ($traces as $trace) {
		if (isset($trace["file"], $trace["line"]) && __FILE__ != $trace["file"]) {
			$label .= $trace["file"] . " (" . $trace["line"] . ")";
			if (++$count >= 3) {
				break;
			} else {
				$label .= "\n";
			}
		}
	}
	simple_logger($var, "----------\n$label\n");
	return true;
}
