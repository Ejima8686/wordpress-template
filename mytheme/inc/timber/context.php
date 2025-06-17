<?php

use Timber\Timber;

add_filter("timber/context", function ($context) {
	$context["options"] = get_fields("options");
	$context["about_post"] = Timber::get_post([
		"name" => "about",
		"post_type" => "page",
	]);

	return $context;
});
