<?php
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\FormController;

$app->group("/lp", function (RouteCollectorProxy $group) {
	$group->get("/", [FormController::class, "request"]);
	$group->post("/confirm/", [FormController::class, "confirm"]);
	$group->post("/send/", [FormController::class, "send"]);
	$group->get("/error/", [FormController::class, "error"]);
	$group->get("/complete/", [FormController::class, "complete"]);

	// 開発環境用のGETルート（上記のリダイレクトルートより後に配置）
	if (IS_DEVELOPMENT && DEBUG_MODE) {
		$group->get("/confirm/", [FormController::class, "confirm"]);
	} else {
		// 確認画面へのGETアクセスを入力画面にリダイレクト
		$group->get("/confirm/", function ($request, $response) {
			return $response->withHeader("Location", $_ENV["DOMAIN"] . "/")->withStatus(302);
		});
	}
});
