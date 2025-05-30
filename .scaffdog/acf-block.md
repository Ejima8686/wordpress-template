---
name: "acf-block"
root: "."
output: "."
ignore: []
questions:
  name:
    message: "ブロックの識別子（英語のスラッグ）を入力してください。"
  title:
    message: "ブロックの表示名（タイトル）を入力してください。"
  description:
    message: "ブロックの説明文を入力してください。"
    initial: "これはカスタムブロックです。"
  icon:
    message: "アイコン名を入力してください（https://developer.wordpress.org/resource/dashicons/#icons-block-editor を参照）"
    initial: "heading"
  category:
    message: "ブロックのカテゴリを選択してください。"
    choices:
      - text
      - media
      - design
      - widgets
      - theme
      - embed
    initial: "text"
---

# Variables

- name: `{{ inputs.name | kebab }}`

# `./{{ theme }}/blocks/{{ name }}/block.json`

```json
{
	"name": "acf/{{ name }}",
	"title": "{{ inputs.title }}",
	"description": "{{ inputs.description }}",
	"category": "{{ inputs.category }}",
	"icon": "{{ inputs.icon }}",
	"keywords": [],
	"supports": {
		"align": false,
		"customClassName": false,
		"mode": true,
		"anchor": false
	},
	"attributes": {},
	"acf": {
		"mode": "edit",
		"renderCallback": "my_acf_block_render_callback"
	}
}
```

# `./{{ theme }}/views/blocks/{{ name }}.twig`

```twig
{%#

Available props

block
fields
is_preview

#%}

<div>
Example
</div>

```

# `./{{ theme }}/acf-json/group_sb_{{ name }}.json`

```json
{
	"key": "group_sb_{{ name }}",
	"title": "Block: {{ inputs.name | pascal }}",
	"fields": [],
	"location": [
		[
			{
				"param": "block",
				"operator": "==",
				"value": "acf/{{ name }}"
			}
		]
	],
	"menu_order": 0,
	"position": "normal",
	"style": "default",
	"label_placement": "top",
	"instruction_placement": "label",
	"hide_on_screen": "",
	"active": true,
	"description": "",
	"show_in_rest": 0,
	"modified": 1666256071
}
```
