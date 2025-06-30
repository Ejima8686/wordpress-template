<?php
/**
 * Yoastのメタ設定に使用する変数を追加
 * - 変数「%%excerpt_description%%」をフィールドに直接入力すること使用できる。
 * @return string 記事本文から抜粋した140文字
 */
add_action('wpseo_register_extra_replacements', function () {
    wpseo_register_var_replacement(
        '%%excerpt_description%%',
        function () {
            $post = Timber\Timber::get_post();

            if (!$post) {
                return '';
            }

            $content = wp_strip_all_tags($post->post_content);
            return mb_substr($content, 0, 140);
        },
        'advanced',
        'First 140 characters of the content, without HTML'
    );
});
