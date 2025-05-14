<?php get_header(); ?>

<main>
	<?php
 set_query_var("title", "ページが見つかりません。");
 get_template_part("partials/page-title");
 ?>
</main>

<?php get_footer(); ?>
