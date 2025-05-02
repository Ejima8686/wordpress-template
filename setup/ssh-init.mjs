import fs from "fs/promises";
import f from "fs";
import path from "path";
import { fileURLToPath } from "url";
import { input, confirm } from "@inquirer/prompts";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const root = path.resolve(__dirname, "..");
const setupDir = path.resolve(root, "setup");
const sshEnvSamplePath = path.resolve(setupDir, ".env.sample");
const sshEnvPath = path.resolve(setupDir, "ssh.env");

/**
 * SSH設定ファイルを作成する。
 * @param {string} keyPath - SSH秘密鍵のパス
 * @returns {Promise<void>}
 */
async function generateSshEnv(keyPath) {
  if (f.existsSync(sshEnvSamplePath)) {
    await fs.unlink(sshEnvSamplePath);
    console.log("🗑️ .env.sample file deleted");
  }

  const content = `SSH_KEY_PATH=${keyPath}\n`;
  await fs.writeFile(sshEnvPath, content);
  console.log("✅ ssh.env file created at:", sshEnvPath);
}

async function main() {
  console.log("🔐 Setup SSH Key");
  const sshKeyPath = await input({ message: "Enter path to your SSH private key:", default: "$HOME/.ssh/id_rsa" });
  await generateSshEnv(sshKeyPath);
}

await main();
