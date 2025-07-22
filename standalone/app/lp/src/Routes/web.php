<?php
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\ArticleWritingServiceController;

$app->group("/article-writing-service/request", function (RouteCollectorProxy $group) {
	$group->get("/", [ArticleWritingServiceController::class, "request"]);
	$group->post("/confirm/", [ArticleWritingServiceController::class, "confirm"]);
	$group->post("/send/", [ArticleWritingServiceController::class, "send"]);
	$group->get("/error/", [ArticleWritingServiceController::class, "error"]);
	$group->get("/complete/", [ArticleWritingServiceController::class, "complete"]);

	// 開発環境用のGETルート（上記のリダイレクトルートより後に配置）
	$isDevelopment = filter_var($_ENV["DEVELOPMENT_MODE"] ?? false, FILTER_VALIDATE_BOOLEAN);
	if ($isDevelopment) {
		$group->get("/confirm/", [ArticleWritingServiceController::class, "confirm"]);
	} else {
		// 確認画面へのGETアクセスを入力画面にリダイレクト
		$group->get("/confirm/", function ($request, $response) {
			return $response
				->withHeader("Location", $_ENV["DOMAIN"] . "/article-writing-service/request/")
				->withStatus(302);
		});
	}
});
