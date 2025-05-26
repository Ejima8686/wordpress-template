<?php
add_action('admin_menu', function () {
    add_menu_page(
        'Dummy Post Generator', 
        'ダミー投稿作成', 
        'manage_options', 
        'dummy-post-generator', 
        'render_dummy_post_generator_page'
    );
});

function render_dummy_post_generator_page() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('generate_dummy_posts')) {
        $post_type = sanitize_text_field($_POST['post_type']);
        $taxonomy = sanitize_text_field($_POST['taxonomy']);
        $post_count = intval($_POST['post_count']);
        $taxonomy_data = array_map(function ($line) {
            return explode('|', $line);
        }, explode(',', sanitize_text_field($_POST['taxonomies'])));

        generate_dummy_posts($post_type, $taxonomy, $post_count, $taxonomy_data);
        echo '<div class="notice notice-success"><p>投稿を生成しました。</p></div>';
    }

    include plugin_dir_path(__FILE__) . 'views/form.php';
}
