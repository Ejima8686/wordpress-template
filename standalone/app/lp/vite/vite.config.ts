import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";
import { resolve } from "path";

export default defineConfig({
	plugins: [tailwindcss()],
	resolve: {
		alias: {
			"@public": resolve(__dirname, "public"),
		},
	},
	build: {
		outDir: "../../../lp/dist", // 出力先を指定
		emptyOutDir: false, // 完全削除しない
		rollupOptions: {
			input: {
				main: resolve(__dirname, "src/main.ts"),
			},
			output: {
				entryFileNames: "[name].js",
				chunkFileNames: "[name].js",
				assetFileNames: "[name][extname]",
			},
		},
	},
	server: {
		port: 5137,
		proxy: {
			// Viteの内部ファイル以外をバックエンドに転送
			"^(?!/@vite|__vite|/src|/node_modules|/dist|/images|/public).*": "http://localhost:8080",
		},
	},
});
