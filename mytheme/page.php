<?php

use Timber\Timber;

$context = Timber::context();
$timber_post = Timber::get_post();
$context["post"] = $timber_post;

render_timber_templates(
	["templates/page-" . $timber_post->post_name . ".twig", "templates/page.twig"],
	$context
);
