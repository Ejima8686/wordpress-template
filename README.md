# wordpress-template-v3
wordpressのテーマ開発用テンプレートです。

## コミットテンプレートのセットアップ
まずはプロジェクトルートで以下のコマンドを実行し、`.github/.gitmessage.txt`をコミットの初期表示に設定します。
```bash
git init
git config commit.template "$(pwd)/.github/.gitmessage.txt"
```
`git commit`を実行すると、`.github/.gitmessage.txt`の内容が展開します。
コミットメッセージの一貫性を保つために、このルールに従ってコミットメッセージを記述してください。

設定を削除する際は以下を実行してください。
```bash
git config --unset commit.template
```
