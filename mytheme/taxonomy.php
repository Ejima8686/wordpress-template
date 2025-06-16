<?php
/**
 * The template for displaying taxonomy archive pages
 *
 * @link https://developer.wordpress.org/themes/template-files-section/taxonomy-templates/
 */

namespace App;

use Timber\Timber;

$context = Timber::context();
$term = get_queried_object();

$context["term"] = $term;
$context["title"] = single_term_title("", false);

$templates = [
	"templates/taxonomy-{$term->taxonomy}-{$term->slug}.twig",
	"templates/taxonomy-{$term->taxonomy}.twig",
	"templates/taxonomy.twig",
];

Timber::render($templates, $context);
