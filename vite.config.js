import { defineConfig, loadEnv } from "vite";
import path from "path";

export default defineConfig(({ mode }) => {
	const env = loadEnv(mode, path.resolve(process.cwd(), ".devcontainer"), "");

	const { THEME_NAME } = env;

	return {
		root: "./",
		build: {
			outDir: `./${THEME_NAME}/build`,
			assetsDir: "",
			manifest: true,
			rollupOptions: {
				input: "./source/index.ts",
			},
		},
		server: {
			hmr: true,
			port: 3000,
			origin: "http://localhost:8080",
		},
	};
});
