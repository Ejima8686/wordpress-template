 <div class="wrap">
        <h1>ダミー記事作成ツール</h1>
        <form method="post">
            <?php wp_nonce_field('generate_dummy_posts'); ?>
            <table class="form-table">
                <tr>
                    <th><label>投稿タイプ</label></th>
                    <td><input type="text" name="post_type" value="mytheme_news" required></td>
                </tr>
                <tr>
                    <th><label>タクソノミー</label></th>
                    <td><input type="text" name="taxonomy" value="mytheme_news_category" required></td>
                </tr>
                <tr>
                    <th><label>投稿数</label></th>
                    <td><input type="number" name="post_count" value="10" min="1" required></td>
                </tr>
                <tr>
                    <th><label>カテゴリ（カンマ区切り:名前|スラッグ）</label></th>
                    <td><input type="text" name="taxonomies" value="セクション #1|section-1,セクション #2|section-2"></td>
                </tr>
            </table>
            <p><input type="submit" class="button button-primary" value="生成する"></p>
        </form>
    </div>
