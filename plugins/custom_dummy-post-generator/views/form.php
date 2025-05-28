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
                <th><label>タクソノミー（任意）</label></th>
                <td>
                    <input type="text" name="taxonomy" placeholder="mytheme_news_category">
                    <p class="description">
                        カテゴリーのタクソノミースラッグを入力してください。<br>
                        空欄の場合はカテゴリの作成・割当はスキップされます。
                    </p>
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
       <p>
            <input type="submit" name="generate_dummy_posts" class="button button-primary" value="生成する">
        </p>
    </form>
</div>
<div style="width: 50%; height: 1px; border-top: 1px solid #000; margin: 40px 0;"></div>
<div class="wrap">
    <h2>ダミー記事一括削除</h2>
    <form method="post">
        <p style="margin-bottom: 20px;" class="description">削除対象となる投稿タイプを入力してください。<br>このプラグインで生成した記事をすべて削除します。</p>
        <input type="text" name="delete_post_type" placeholder="mytheme_news" required>
        <?php wp_nonce_field("delete_dummy_posts_action"); ?>
        <p>
            <input type="submit" name="delete_dummy_posts" class="button button-primary" value="削除する">
        </p>
    </form>
</div>
<div style="width: 50%; height: 1px; border-top: 1px solid #000; margin: 40px 0;"></div>
<div class="wrap">
    <h2>ダミーカテゴリ一括削除</h2>
    <form method="post">
        <p style="margin-bottom: 20px;" class="description">削除対象となるタクソノミースラッグを入力してください。<br>このプラグインで生成したカテゴリーをすべて削除します。</p>
        <input type="text" name="delete_taxonomy" placeholder="mytheme_news_category" required>
        <?php wp_nonce_field("delete_dummy_terms_action"); ?>
        <p>
            <input type="submit" name="delete_dummy_terms" class="button button-primary" value="削除する">
        </p>
    </form>
</div>
