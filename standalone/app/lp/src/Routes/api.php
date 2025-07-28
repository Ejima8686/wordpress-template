<?php

/* APIを作成する場合に使用 */
$app->group("/api", function ($app) {
	$app->get("/", App\Controllers\ApiController::class . ":index");
});
