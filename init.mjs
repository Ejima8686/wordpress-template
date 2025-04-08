import fs from "fs/promises";
import f from "fs";
import path from "path";
import { input, password, confirm } from "@inquirer/prompts";

const root = path.dirname(new URL(import.meta.url).pathname);
const envFilePath = path.resolve(root, ".devcontainer/.env");

// ========================
// Functions
// ========================

function getThemeDirName() {
	const dirs = f.readdirSync(root).filter((file) =>
		f.statSync(path.join(root, file)).isDirectory()
	);

	for (const dir of dirs) {
		if (f.existsSync(path.join(root, dir, "theme.json"))) {
			return dir;
		}
	}
	return "";
}

async function generateEnvFile(themeName) {
	const content = `THEME_NAME=${themeName}\nVITE_THEME_NAME=${themeName}`;
	await fs.writeFile(envFilePath, content);
	console.log("✅ .env を生成しました:", themeName);
}

async function renameTheme(themeName) {
	const oldDir = path.resolve(root, "mytheme");
	const newDir = path.resolve(root, themeName);
	await fs.rename(oldDir, newDir);
	console.log(`📁 テーマフォルダを '${themeName}' にリネームしました`);
}

async function generateThemeStyle(themeName) {
	const themeStylePath = path.resolve(root, themeName, "style.css");
	const content = `/* Theme Name: ${themeName} */`;
	await fs.writeFile(themeStylePath, content);
	console.log("📝 style.css を生成しました");
}

async function updateEnvFile(key, value) {
	const data = await fs.readFile(envFilePath, "utf8");
	const envData = Object.fromEntries(
		data.trim().split("\n").map((line) => line.split("="))
	);
	envData[key] = value;

	const newContent = Object.entries(envData)
		.map(([k, v]) => `${k}=${v}`)
		.join("\n");

	await fs.writeFile(envFilePath, newContent);
	console.log("🔧 .env ファイルを更新しました。");
}

async function generateAuthJson(token) {
	const authJsonFilePath = path.resolve(root, "auth.json");
	const content = `{
  "http-basic": {
    "connect.advancedcustomfields.com": {
      "username": "${token}",
      "password": "https://example.com"
    }
  }
}`;
	await fs.writeFile(authJsonFilePath, content);
	console.log("✅ auth.json を生成しました");
}

// ========================
// Main Interaction
// ========================
async function main() {

	// STEP 1: Initialization confirmation
	console.log("\n🟦 STEP 1: Initialization");
	const confirmInit = await confirm({ message: `Initialize?`, default: false });
	if (!confirmInit) {
		console.log("❌ 初期化をキャンセルしました");
		process.exit(0);
	}

	// STEP 2: Generate initial .env
	console.log("\n🟦 STEP 2: .envファイルの初期生成");
	let themeName = getThemeDirName();
	await generateEnvFile(themeName);

	// STEP 3: Rename theme if needed
	console.log("\n🟦 STEP 3: テーマ名のリネーム確認");
	const confirmRename = await confirm({
		message: `Rename theme name? current: ${themeName}`,
		default: false,
	});

	if (confirmRename) {
		themeName = await input({ message: "New theme name:", default: themeName });
		await renameTheme(themeName);
		await generateThemeStyle(themeName);
		await updateEnvFile("THEME_NAME", themeName);
		await updateEnvFile("VITE_THEME_NAME", themeName);
	}

	// STEP 4: ACF PRO Token Setup
	console.log("\n🟦 STEP 4: ACF PRO 認証情報の設定");
	const confirmAuth = await confirm({ message: `Generate auth.json?` });
	if (confirmAuth) {
		const token = await password({ message: "Input ACF PRO LICENCE KEY..." });
		await generateAuthJson(token);
		await updateEnvFile("ACF_PRO_KEY", token);
	}

	console.log("\n✅ 初期化が完了しました！");
}

await main();
