<div class="wrap">
    <h1>ダミー記事作成ツール</h1>
    <form method="post">
        <?php wp_nonce_field("generate_dummy_posts"); ?>
        <table class="form-table">
            <tr>
                <th><label>投稿タイプ</label></th>
                <td>
                    <input type="text" name="post_type" placeholder="mytheme_news" required>
                    <p class="description">生成対象の投稿タイプを入力してください。</p>
                </td>
            </tr>
            <tr>
                <th><label>タクソノミー</label></th>
                <td>
                    <input type="text" name="taxonomy" placeholder="mytheme_news_category" required>
                    <p class="description">カテゴリーのタクソノミースラッグを入力してください。</p>
                </td>
            </tr>
            <tr>
                <th><label>記事数</label></th>
                <td>
                    <input type="number" name="post_count" placeholder="10" min="1" required>
                    <p class="description">生成したいダミー記事の数を指定します。</p>
                </td>
            </tr>
            <tr>
                <th><label>カテゴリ</label></th>
                <td>
                    <textarea rows="5" cols="33" name="taxonomies">セクション #1|section-1,
セクション #2|section-2</textarea>
                    <p class="description">
                        「名前|スラッグ」の形式で入力してください。<br>
                        改行またはカンマ（,）で複数カテゴリを区切ることができます。<br>
                        同じスラッグが既に存在する場合はスキップされます。
                    </p>
                </td>
            </tr>
        </table>
        <p><input type="submit" class="button button-primary" value="生成する"></p>
    </form>
</div>
