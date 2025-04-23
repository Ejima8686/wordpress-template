<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<<?php bloginfo('charset'); ?>" />
		<meta name="viewport" content="width=device-width" />
		<meta name="format-detection" content="telephone=no" />

		<script type="module" src="http://localhost:3000/@vite/client"></script>
  		<script type="module" src="http://localhost:3000/source/index.ts"></script>

		<link rel="stylesheet" href=/mytheme/build/index-0xo6n0od.css">
		<?php wp_head(); ?>
	</head>
    <?php
		get_template_part('partials/frame/site-header');
	?>
