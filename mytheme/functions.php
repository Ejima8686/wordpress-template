<?php

namespace WordPressStarter\Theme;

define("THEME_NAME", basename(__DIR__));

function build_assets(): string {
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
                $html .= sprintf('<script type="module" src="%s"></script>', $base_uri . $manifest[$dynamicImport]["file"]) . "\n";
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

add_action('wp_head', function () {
	error_log('wp_head hook fired!');
    echo \WordPressStarter\Theme\build_assets();
});