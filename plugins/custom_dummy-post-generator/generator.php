<?php
require_once __DIR__ . '/vendor/autoload.php';

function generate_dummy_posts($post_type, $taxonomy, $post_count = 10, $taxonomy_data = []) {
    $faker = Faker\Factory::create('ja_JP');

    // カテゴリー作成
    $term_ids = [];
    foreach ($taxonomy_data as [$name, $slug]) {
        $term = get_term_by('slug', $slug, $taxonomy);
        if (!$term) {
            $term_id = wp_insert_term($name, $taxonomy, ['slug' => $slug]);
            if (!is_wp_error($term_id)) {
                $term_ids[] = $term_id['term_id'];
            }
        } else {
            $term_ids[] = $term->term_id;
        }
    }

    // アイキャッチ画像作成
    $image_urls = array_map(fn() => "https://picsum.photos/800/600.jpg?random=" . uniqid(), range(1, 5));
    $image_ids = [];
    foreach ($image_urls as $url) {
        $tmp = download_url($url);
        if (is_wp_error($tmp)) continue;

        $file = [
            'name'     => basename($url),
            'type'     => 'image/jpeg',
            'tmp_name' => $tmp,
            'error'    => 0,
            'size'     => filesize($tmp),
        ];

        $overrides = ['test_form' => false];
        $results = wp_handle_sideload($file, $overrides);

        if (!isset($results['url'])) continue;

        $attachment = [
            'post_mime_type' => $results['type'],
            'post_title'     => sanitize_file_name($results['file']),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ];

        $attach_id = wp_insert_attachment($attachment, $results['file']);
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attach_data = wp_generate_attachment_metadata($attach_id, $results['file']);
        wp_update_attachment_metadata($attach_id, $attach_data);

        $image_ids[] = $attach_id;
    }

    // 投稿作成
    for ($i = 1; $i <= $post_count; $i++) {
        $title = "#{$i}. " . $faker->realText(random_int(50, 150));

        $post_id = wp_insert_post([
            'post_type'    => $post_type,
            'post_title'   => $title,
            'post_content' => generate_dummy_content($faker),
            'post_status'  => 'publish',
        ]);

        if (is_wp_error($post_id)) continue;

        if (!empty($image_ids)) {
            set_post_thumbnail($post_id, $image_ids[array_rand($image_ids)]);
        }

        if (!empty($term_ids)) {
            $selected = array_rand(array_flip($term_ids), random_int(1, min(3, count($term_ids))));
            wp_set_object_terms($post_id, (array) $selected, $taxonomy);
        }
    }
}

function generate_dummy_content($faker) {
    $paragraphs = [];
    $num_paragraphs = random_int(3, 5);
    for ($i = 0; $i < $num_paragraphs; $i++) {
        $paragraphs[] = '<!-- wp:paragraph --><p>' . $faker->realText(random_int(100, 300)) . '</p><!-- /wp:paragraph -->';
    }
    $paragraphs[] = "<p><a href='https://example.com' target='_blank'>詳しくはこちら</a></p>";
    return implode("\n", $paragraphs);
}
