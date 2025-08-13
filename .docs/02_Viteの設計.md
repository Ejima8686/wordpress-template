# Viteの設計


---

## 目次

- [概要](#概要)
- [1. Viteの設定](#1-viteの設定)
- [2. ビルド処理](#2-ビルド処理)
- [3. 開発環境と本番環境で読み込むファイルを分ける](#3-開発環境と本番環境で読み込むファイルを分ける)

---

## 概要

当開発環境では、**Vite** を採用しています。

https://vitejs.dev/

Viteは、モダンなフロントエンド開発ツールで、高速な開発サーバーと最適化されたビルド機能を提供します。

## 1. Viteの設定

Viteの設定は、プロジェクトルートの `vite.config.ts` で管理します。

```typescript
import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig({
  build: {
    outDir: 'mytheme/build',
    rollupOptions: {
      input: {
        index: resolve(__dirname, 'source/index.ts'),
      },
    },
  },
  server: {
    host: true,
    port: 3000,
  },
})
```

この設定により、以下のことが実現されます：

- **ビルド出力先**: `mytheme/build` ディレクトリにビルドファイルが出力されます
- **エントリーポイント**: `source/index.ts` がメインのエントリーポイントになります
- **開発サーバー**: `localhost:3000` で開発サーバーが起動します

## 2. ビルド処理

ビルド処理は、`package.json` のスクリプトで管理します。

```json
{
  "scripts": {
    "base:dev": "vite --host",
    "base:build": "vite build", ←本番用のビルドを作成します
    "format": "prettier . --write"
  }
}
```

1. ビルドコマンドを実行すると、テーマ内に「/build」のディレクトリが生成されます。

```
build
├── .vite
│   └── manifest.json　←CSS、JSファイルをインポートします
├── index-⚪︎⚪︎⚪︎⚪︎⚪︎.js
└── index-⚪︎⚪︎⚪︎⚪︎⚪︎.css

※ファイル名の⚪︎⚪︎⚪︎⚪︎⚪︎の部分は変更があるたびにハッシュが付与され、毎回ファイル名が変わります
```

CSS、JavaScriptの変更があるたびにビルドされるファイルのハッシュが変わるため、manifest.jsonでビルドされたCSS、JavaScriptインポートした上でページのhead内でmanifest.jsonを読み込むようにしています。

manifest.jsonを読み込むことで、ビルドされるCSS、JavaScriptのファイル名を動的に取得できるようになっています。

## 3. 開発環境と本番環境で読み込むファイルを分ける

functions.phpで開発環境であるか本番環境であるかを判定し、読み込むファイルを分けています。

●開発環境であるかどうかの判定

```php
/**
 * 開発環境であるか判定し、真偽値を返す
 * @return bool
 */
function is_dev(): bool {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    return str_contains($host, 'localhost') || str_contains($host, '.local');
}
```

●読み込むファイルを分ける処理

```php
function build_assets(): string {
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
    echo \WordPressStarter\Theme\build_assets();
});
```

開発環境の場合は、プロジェクトルートの/source/index.tsを読み込みます。

```php
if (is_dev()) {
        return <<<HTML
        <!-- development (Vite) -->
        <script type="module" src="http://localhost:3000/@vite/client"></script>
        <script type="module" src="http://localhost:3000/source/index.ts"></script>
        HTML;
    }
```

本番環境では、テーマ内に生成された「/build」ディレクトリ内のcss、jsファイルを読み込みます。

```php
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
```

manifest.jsonを連想配列として読み込み、「/build」内のcss、jsのファイル名を取得します。

取得したcss、jsのファイル名に対し、各ファイルを読み込んだlinkタグ、scriptタグを生成する処理をしています。

```php
add_action('wp_head', function () {
    echo \WordPressStarter\Theme\build_assets();
});
```

ページのhead内で生成されたlinkタグ、scriptタグを呼び出します。
