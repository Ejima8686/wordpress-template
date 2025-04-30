<?php get_header(); ?>

<main>
	<?php
		set_query_var('title', get_the_title());
		get_template_part('partials/page-title');
	?>

	<?php
		if (has_post_thumbnail()) {
			echo wp_get_attachment_image(
				get_post_thumbnail_id(),
				'full',
				false,
				[
					'class' => 'w-full min-h-[14rem] object-cover aspect-3/1'
				]
			);
		}
	?>

	<div class="container mt-16 prose">
		<?php the_content(); ?>
	</div>
</main>

<?php get_footer(); ?>
