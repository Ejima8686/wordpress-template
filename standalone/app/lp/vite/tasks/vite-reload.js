#!/usr/bin/env node

import chokidar from "chokidar";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

// ESモジュール用の__dirname
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// HMRをトリガーするためのJavaScriptファイル
const HMR_TRIGGER_FILE = path.resolve(__dirname, "../src/hmr-trigger.ts");

// HMRトリガーファイルを作成
function createHmrTriggerFile() {
	const content = `// HMR trigger file - auto-generated
// This file is updated when Twig files change to trigger Vite HMR
export const lastUpdate = Date.now();
`;

	try {
		fs.writeFileSync(HMR_TRIGGER_FILE, content);
	} catch (error) {
		console.error("Failed to update HMR trigger file:", error.message);
	}
}

// 初期化時にHMRトリガーファイルを作成
createHmrTriggerFile();

function triggerViteReload() {
	// HMRトリガーファイルを更新
	createHmrTriggerFile();
}

// Twigファイルの監視
const watcher = chokidar.watch("../src/Views/**/*.twig", {
	persistent: true,
	ignoreInitial: true,
	awaitWriteFinish: {
		stabilityThreshold: 100,
		pollInterval: 100,
	},
});

watcher.on("change", (path) => {
	triggerViteReload();
});

watcher.on("error", (error) => {
	console.error("Watcher error:", error);
});
