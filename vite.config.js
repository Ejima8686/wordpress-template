import { defineConfig, loadEnv } from 'vite';
import path from 'path';

export default defineConfig(({ mode }) => {
  // プロジェクトルートから .devcontainer/.env を読み込む
  const env = loadEnv(mode, path.resolve(process.cwd(), '.devcontainer'), '');

  const { THEME_NAME } = env;

  return {
    build: {
      outDir: `./${THEME_NAME}/build`,
      assetsDir: "",
      // manifest: true,
      rollupOptions: {
        input: "./source/main.css",
      },
    },
    server: {
      hmr: true,
      port: 3000,
      origin: "http://localhost:8080",
    },
  };
});
