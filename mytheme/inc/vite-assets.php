<?php

namespace WordPressStarter\Theme;

/**
 * 開発環境であるか判定し、真偽値を返す
 * @return bool
 */
function is_dev(): bool
{
	$host = $_SERVER["HTTP_HOST"] ?? "";
	return str_contains($host, "localhost") || str_contains($host, ".local");
}

/**
 * ViteによってビルドされたJS/CSSアセットをHTMLとして出力する
 *
 * - 開発環境（localhostまたは.local）ではVite Dev Serverへのscriptタグを出力
 * - 本番環境では`.vite/manifest.json`をもとにビルドされたアセットを出力
 *
 * @return string アセットを読み込むための<script>および<link>タグ群（HTML）
 */
function build_assets(): string
{
	if (is_dev()) {
		return <<<HTML
<!-- development (Vite) -->
<script type="module" src="http://localhost:3000/@vite/client"></script>
<script type="module" src="http://localhost:3000/source/index.ts"></script>
HTML;
	}

	$manifest_path = __DIR__ . "/build/.vite/manifest.json";
	if (!file_exists($manifest_path)) {
		return "<!-- vite manifest not found -->";
	}

	$manifest = json_decode(file_get_contents($manifest_path), true);
	if (!$manifest || !isset($manifest["source/index.ts"])) {
		return "<!-- invalid vite manifest -->";
	}

	$entry = $manifest["source/index.ts"];
	$base_uri = get_template_directory_uri() . "/build/";

	$html = "<!-- production build -->\n";
	$html .= sprintf('<script type="module" src="%s"></script>', $base_uri . $entry["file"]) . "\n";

	if (!empty($entry["dynamicImports"])) {
		foreach ($entry["dynamicImports"] as $dynamicImport) {
			if (!empty($manifest[$dynamicImport]["file"])) {
				$html .=
					sprintf(
						'<script type="module" src="%s"></script>',
						$base_uri . $manifest[$dynamicImport]["file"]
					) . "\n";
			}
		}
	}

	if (!empty($entry["css"])) {
		foreach ($entry["css"] as $css) {
			$html .= sprintf('<link rel="stylesheet" href="%s" />', $base_uri . $css) . "\n";
		}
	}

	return $html;
}

add_action("wp_head", function () {
	echo \WordPressStarter\Theme\build_assets();
});
