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
	console.log("✅ .env file generated:", themeName);
}

async function renameTheme(themeName) {
	const oldDir = path.resolve(root, "mytheme");
	const newDir = path.resolve(root, themeName);
	await fs.rename(oldDir, newDir);
	console.log(`📁 Theme folder renamed to '${themeName}'`);
}

async function updateDevcontainerFiles(themeName) {
	const devcontainerPath = path.resolve(root, ".devcontainer/devcontainer.json");
	const composePath = path.resolve(root, ".devcontainer/docker-compose.yml");

	let devcontainerData = await fs.readFile(devcontainerPath, "utf8");
	devcontainerData = devcontainerData.replace(/mytheme/g, themeName);
	await fs.writeFile(devcontainerPath, devcontainerData);
	console.log("🛠️ devcontainer.json updated");

	let composeData = await fs.readFile(composePath, "utf8");
	composeData = composeData.replace(/\.\.\/mytheme/g, `../${themeName}`);
	composeData = composeData.replace(/\/var\/www\/html\/wp-content\/themes\/mytheme/g, `/var/www/html/wp-content/themes/${themeName}`);
	await fs.writeFile(composePath, composeData);
	console.log("🛠️ docker-compose.yml updated");
}

async function generateThemeStyle(themeName) {
	const themeStylePath = path.resolve(root, themeName, "style.css");
	const content = `
	/* 
	Theme Name: ${themeName} 
	*/`;
	await fs.writeFile(themeStylePath, content);
	console.log("📝 style.css generated");
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
	console.log("🔧 .env file updated.");
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
	console.log("✅ auth.json generated");
}

// ========================
// Main Interaction
// ========================
async function main() {
	console.log("\n🟦 STEP 1: Initialization");
	const confirmInit = await confirm({ message: `Initialize?`, default: false });
	if (!confirmInit) {
		console.log("❌ Initialization cancelled.");
		process.exit(0);
	}
	let themeName = getThemeDirName();
	await generateEnvFile(themeName);

	console.log("\n🟦 STEP 2: Rename theme directory");
	const confirmRename = await confirm({
		message: `Rename theme directory? Current name: ${themeName}`,
		default: false,
	});

	if (confirmRename) {
		themeName = await input({ message: "New theme name:", default: themeName });
		await renameTheme(themeName);
		await updateDevcontainerFiles(themeName);
		await generateThemeStyle(themeName);
		await updateEnvFile("THEME_NAME", themeName);
		await updateEnvFile("VITE_THEME_NAME", themeName);
	}

	console.log("\n🟦 STEP 3: ACF PRO license setup");
	const confirmAuth = await confirm({ message: `Do you want to generate auth.json?` });
	if (confirmAuth) {
		const token = await password({ message: "Enter your ACF PRO license key:" });
		await generateAuthJson(token);
		await updateEnvFile("ACF_PRO_KEY", token);
	}

	console.log("\n✅ Initialization complete!");
}

await main();
