<?php

use Timber\Timber;

$templates = ["templates/index.twig"];

if (is_home()) {
	array_unshift($templates, "templates/front-page.twig", "templates/home.twig");
}

$context = Timber::context();

Timber::render($templates, $context);
