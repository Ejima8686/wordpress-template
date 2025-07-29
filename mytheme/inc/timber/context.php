<?php

use Timber\Timber;

add_filter("timber/context", function ($context) {
	$context["IS_DEVELOPMENT"] = is_dev();
	$context["IS_VITE_RUNNING"] = check_vite_connection();
	$context["options"] = get_fields("options");
	$context["about_post"] = Timber::get_post([
		"name" => "about",
		"post_type" => "page",
	]);

	return $context;
});
