<?php get_header(); ?>

<main>
	<?php
 set_query_var("title", get_bloginfo("name"));
 set_query_var("description", get_bloginfo("description"));
 get_template_part("partials/page-title");
 ?>

	<img
		style="width: 100%; min-height: 14rem; object-fit: cover; aspect-ratio: 3 / 1;"
		src="https://picsum.photos//768/400?grayscale"
		alt=""
		decoding="async"
	/>
</main>

<?php get_footer(); ?>
