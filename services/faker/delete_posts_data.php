<?php

echo "Deleting...\n";
echo "Deleting posts\n";

$post_type_list = _wp("post-type list --public=1 --field=name --format=json");
$post_type_list = join(",", json_decode($post_type_list));

$post_ids = _wp("post list --post_type='$post_type_list' --format=ids");

if (!empty($post_ids)) {
	_wp("post delete $post_ids --force");
}

echo "Deleting terms\n";

$tax_type_list = _wp("taxonomy list --public=1 --fields=name,public --format=json");
$tax_type_list = array_filter(json_decode($tax_type_list, true), function ($item) {
	return $item["public"];
});

$tax_type_list = array_map(function ($item) {
	return $item["name"];
}, $tax_type_list);

foreach ($tax_type_list as $item) {
	echo "Deleting term $item\n";
	$term_id = _wp("term list '$item' --field=term_id");
	$term_id = explode("\n", $term_id);

	if (empty($term_id)) {
		continue;
	}

	foreach ($term_id as $id) {
		$id = (int) $id;
		_wp("term delete $item $id");
	}
}
