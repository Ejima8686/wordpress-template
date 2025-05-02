#!/bin/bash
# ================================
# SSH設定スクリプト（ホスト用）
# - setup/ssh.env を読み込み
# - ssh-agent を起動
# - SSH鍵を ssh-agent に登録
# - ~/.ssh/config を初回のみ生成
# ================================

ENV_PATH="$(dirname "$0")/ssh.env"

if [[ -f "$ENV_PATH" ]]; then
  echo "📦 Loading config: $ENV_PATH"
  set -a
  source "$ENV_PATH"
  set +a
else
  echo "⚠ Config not found: $ENV_PATH"
  echo "Please create setup/ssh.env"
  exit 1
fi

KEY_PATH="${SSH_KEY_PATH:-}"
if [[ -z "$KEY_PATH" ]]; then
  echo "❌ SSH_KEY_PATH is not set (in ssh.env)"
  exit 1
fi
echo "🔐 Using SSH key: $KEY_PATH"

eval "$(ssh-agent -s)"
echo "Starting ssh-agent..."

if [[ -f "$KEY_PATH" ]]; then
  ssh-add "$KEY_PATH"
  echo "✅ SSH key added to agent: $KEY_PATH"
else
  echo "❌ SSH key not found: $KEY_PATH"
  exit 1
fi

SSH_CONFIG_PATH="${SSH_CONFIG_PATH:-}"
if [[ ! -f "$SSH_CONFIG_PATH" ]]; then
  echo "❌ SSH_CONFIG_PATH is not set (in ssh.env)"
  exit 1
fi
echo "⚙️ SSH config path: $SSH_CONFIG_PATH"

if ! grep -qE "^Host github\.com$" "$SSH_CONFIG_PATH"; then
  echo "🛠 Adding Host github.com to SSH config"
  cat <<EOF >> "$SSH_CONFIG_PATH"

Host github.com
  HostName github.com
  User git
  IdentityFile $KEY_PATH
  ForwardAgent yes
  AddKeysToAgent yes
EOF
fi

echo "🎉 SSH setup complete."
