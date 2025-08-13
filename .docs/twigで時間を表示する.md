# twigで時間を表示する


---

## 時間表記

[DateTimeInterface::format](https://www.php.net/manual/ja/datetime.format.php)

2023.01.11

```javascript
<time datetime="{{ post.date('Y-m-d H:i:s') }}" >    {{ function('date', 'Y.m.d', post.date('U')) }}</time>
```

Jul 25, 2022

```javascript
<time datetime="{{ post.date|date('Y-m-d H:i:s') }}">    {{ function('date', 'M d, Y', post.date('U')) }}</time>
```
