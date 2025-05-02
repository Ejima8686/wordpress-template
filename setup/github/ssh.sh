#!/bin/bash
# ================================
# コンテナでgit pushを実行するために、SSHの設定をします。（ホスト用）
# - setup/github/ssh.env を読み込み
# - ssh-agent を起動
# - SSH鍵を ssh-agent に登録
# ================================

ENV_PATH="$(dirname "$0")/ssh.env"

if [[ -f "$ENV_PATH" ]]; then
  echo "📦 Loading config: $ENV_PATH"
  set -a
  source "$ENV_PATH"
  set +a
else
  echo "⚠ Config not found: $ENV_PATH"
  echo "Please create setup/github/ssh.env"
  exit 1
fi

KEY_PATH="${SSH_KEY_PATH:-}"
if [[ -z "$KEY_PATH" ]]; then
  echo "❌ SSH_KEY_PATH is not set (in ssh.env)"
  exit 1
fi
if [ ! -f "$KEY_PATH" ]; then
  echo "❌ SSH key file not found at: $KEY_PATH"
  exit 1
fi
echo "🔐 Using SSH key: $KEY_PATH"

# ssh-agent が使えるように初期化します。
# https://code.visualstudio.com/remote/advancedcontainers/sharing-git-credentials#_using-ssh-keys:~:text=Copy-,Linux%3A,-First%2C%20start%20the
if [ -z "$SSH_AUTH_SOCK" ]; then
   RUNNING_AGENT="`ps -ax | grep 'ssh-agent -s' | grep -v grep | wc -l | tr -d '[:space:]'`"
   if [ "$RUNNING_AGENT" = "0" ]; then
        ssh-agent -s &> $KEY_PATH
   fi
   eval `cat $KEY_PATH`
fi

ssh-add $KEY_PATH
echo "🎉 SSH setup complete."
