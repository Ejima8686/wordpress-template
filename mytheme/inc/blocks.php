<?php

namespace WordPressStarter\Theme;

use Timber\Timber;

/**
 * カスタムブロックのレンダリング
 */
add_action(
	"init",
	function () {
		foreach (glob(dirname(__DIR__) . "/blocks/*", GLOB_ONLYDIR) as $dir) {
			register_block_type($dir);
		}
	},
	5
);
