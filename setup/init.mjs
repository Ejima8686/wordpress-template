import fs from "fs/promises";
import f from "fs";
import path from "path";
import { fileURLToPath } from "url";
import { input, password, confirm } from "@inquirer/prompts";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const root = path.resolve(__dirname, "..");
const envSampleFilePath = path.resolve(root, ".devcontainer/.env.sample");
const envFilePath = path.resolve(root, ".devcontainer/.env");

/**
 * 現在のテーマディレクトリ名を取得する。
 * ディレクトリ内に theme.json が存在するディレクトリを対象とする。
 * @returns {string} テーマディレクトリ名（例: "mytheme"）
 */
function getThemeDirName() {
	const dirs = f
		.readdirSync(root)
		.filter((file) => f.statSync(path.join(root, file)).isDirectory());

	for (const dir of dirs) {
		if (f.existsSync(path.join(root, dir, "theme.json"))) {
			return dir;
		}
	}
	return "";
}

/**
 * .env ファイルを生成する。
 * @param {string} themeName - テーマ名
 * @returns {Promise<void>}
 */
async function generateEnvFile(themeName) {
	const content = `THEME_NAME=${themeName}\nVITE_THEME_NAME=${themeName}`;
	if (f.existsSync(envSampleFilePath)) {
		f.unlinkSync(envSampleFilePath);
		console.log("🗑️ .env.sample file deleted");
	}
	await fs.writeFile(envFilePath, content);
	console.log("✅ .env file generated:", themeName);
}

/**
 * テーマディレクトリの名称を変更する。
 * @param {string} themeName - 新しいテーマ名
 * @returns {Promise<void>}
 */
async function renameTheme(themeName) {
	const currentDirName = getThemeDirName();
	const oldDir = path.resolve(root, currentDirName);
	const newDir = path.resolve(root, themeName);

	if (currentDirName === themeName) {
		console.log("🚫 New name matches the current theme name. Skipping rename.");
		return;
	}

	await fs.rename(oldDir, newDir);
	console.log(`📁 Theme folder renamed to '${themeName}'`);
}

/**
 * テーマディレクトリ内に style.css を生成する。
 * @param {string} themeName - テーマ名
 * @returns {Promise<void>}
 */
async function generateThemeStyle(themeName) {
	const themeStylePath = path.resolve(root, themeName, "style.css");
	const content = `
	/*
	Theme Name: ${themeName}
	*/`;
	await fs.writeFile(themeStylePath, content);
	console.log("📝 style.css generated");
}

/**
 * .env ファイル内のキーの値を更新する。
 * @param {string} key - 環境変数のキー名
 * @param {string} value - 設定する値
 * @returns {Promise<void>}
 */
async function updateEnvFile(key, value) {
	const data = await fs.readFile(envFilePath, "utf8");
	const envData = Object.fromEntries(
		data
			.trim()
			.split("\n")
			.map((line) => line.split("=")),
	);
	envData[key] = value;

	const newContent = Object.entries(envData)
		.map(([k, v]) => `${k}=${v}`)
		.join("\n");

	await fs.writeFile(envFilePath, newContent);
	console.log("🔧 .env file updated.");
}

/**
 * ACF PRO用の auth.json を生成する。
 * @param {string} token - ACF PRO ライセンスキー
 * @returns {Promise<void>}
 */
async function generateAuthJson(token) {
	const authJsonFilePath = path.resolve(root, ".devcontainer/auth.json");
	const content = `{
  "http-basic": {
    "connect.advancedcustomfields.com": {
      "username": "${token}",
      "password": "https://example.com"
    }
  }
}`;
	await fs.writeFile(authJsonFilePath, content);
	console.log("✅ auth.json generated");
}

/**
 * 対話的に初期化処理を実行するメイン関数。
 * テーマ名取得 → テーマのリネーム → style.css 生成 → .envとauth.jsonを生成。
 * @returns {Promise<void>}
 */
// 対話的に初期化処理を実行するメイン関数
async function main() {
	console.log("\n🟦 ステップ1: 初期化");
	const confirmInit = await confirm({ message: `初期化を開始しますか？`, default: false });
	if (!confirmInit) {
		console.log("❌ 初期化をキャンセルしました。");
		process.exit(0);
	}

	let themeName = getThemeDirName();
	await generateEnvFile(themeName);

	console.log("\n🟦 ステップ2: テーマディレクトリの名前変更");
	const confirmRename = await confirm({
		message: `テーマディレクトリの名前を変更しますか？ 現在の名前: ${themeName}`,
		default: false,
	});

	if (confirmRename) {
		themeName = await input({ message: "新しいテーマ名を入力してください：", default: themeName });
		await renameTheme(themeName);
		await generateThemeStyle(themeName);
		await updateEnvFile("THEME_NAME", themeName);
		await updateEnvFile("VITE_THEME_NAME", themeName);
	}

	console.log("\n🟦 ステップ3: ACF PRO ライセンスの設定");
	const confirmAuth = await confirm({ message: `auth.jsonを新しく生成しますか？` });
	if (confirmAuth) {
		const token = await password({ message: "ACF PRO ライセンスキーを入力してください：" });
		await generateAuthJson(token);
		await updateEnvFile("ACF_PRO_KEY", token);
	}

	console.log("\n✅ 初期化が完了しました！Dev Container を起動して、開発を開始しましょう！");
}
await main();
