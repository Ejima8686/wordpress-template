import { defineConfig, loadEnv } from "vite";
import path from "path";

function mergeEnv(mode) {
	const env = loadEnv(mode, path.resolve(process.cwd(), ".devcontainer"), "");

	return {
		...env,
	};
}

export default defineConfig(({ mode }) => {
	const env = mergeEnv(mode);

	return {
		root: "./",
		define: {
			__THEME__: JSON.stringify(env.VITE_THEME_NAME),
		},
		build: {
			outDir: `./${env.VITE_THEME_NAME}/build`,
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
