<?php
require_once __DIR__ . "/vendor/autoload.php";

use Dotenv\Dotenv;
use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Csrf\Guard;
use Slim\Psr7\Factory\ResponseFactory;
use Twig\Extension\DebugExtension;
use App\Extensions\TwigExtension;
use App\Models\ErrorInfo;

/*
 * デバッグモードの設定
 */
if (isset($_ENV["DEBUG_MODE"]) && $_ENV["DEBUG_MODE"] == 1) {
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
} else {
	ini_set("display_errors", 0);
}

/**
 * 環境変数の読み込み
 */
if (file_exists(__DIR__ . "/config/.env")) {
	$dotenv = Dotenv::createImmutable(__DIR__ . "/config/");
	$dotenv->load();
} else {
	error_log("Environment file not found");
	exit(1);
}

/**
 * エラー出力先の設定
 */
if (isset($_ENV["ERROR_LOG_PATH"]) && file_exists($_ENV["ERROR_LOG_PATH"])) {
	ini_set("error_log", $_ENV["ERROR_LOG_PATH"]);
	ini_set("log_errors", 1);
} else {
	/* エラー出力先がない場合は強制終了 */
	echo "Error log path not found. Please set ERROR_LOG_PATH in .env file.";
	exit(1);
}

/* DIコンテナの作成 */
$container = new Container();

/* Twigを登録 */
$container->set(Twig::class, function () {
	$twig = Twig::create(__DIR__ . "/src/Views");
	$twig->addExtension(new DebugExtension());
	$twig->addExtension(new TwigExtension());
	return $twig;
});

// CSRF Guardの登録
$container->set(Guard::class, function () {
	$responseFactory = new ResponseFactory();
	$guard = new Guard($responseFactory);

	// 開発環境かつデバッグモードではCSRF検証をスキップ
	$skipCSRF =
		filter_var($_ENV["IS_DEVELOPMENT"] ?? false, FILTER_VALIDATE_BOOLEAN) &&
		filter_var($_ENV["DEBUG_MODE"] ?? false, FILTER_VALIDATE_BOOLEAN);
	if ($skipCSRF) {
		$guard->setFailureHandler(function (
			Psr\Http\Message\ServerRequestInterface $request,
			Psr\Http\Server\RequestHandlerInterface $handler
		) use ($responseFactory) {
			// 開発環境ではCSRFエラーを無視して処理を続行
			return $handler->handle($request);
		});
	} else {
		// 本番環境ではCSRFエラーハンドラーを設定
		$guard->setFailureHandler(function (
			Psr\Http\Message\ServerRequestInterface $request,
			Psr\Http\Server\RequestHandlerInterface $handler
		) use ($responseFactory) {
			// セッションにエラー情報を保存
			$errorInfo = ErrorInfo::csrfError();
			$_SESSION["error_info"] = $errorInfo->toArray();

			$response = $responseFactory->createResponse();
			return $response
				->withHeader("Location", "/article-writing-service/request/error/")
				->withStatus(302);
		});
	}

	return $guard;
});

/* コントローラーの登録 */
$container->set(App\Controllers\FormController::class, function ($c) {
	return new App\Controllers\FormController($c->get(Twig::class), $c->get(Guard::class));
});

/* アプリケーションの作成 */
AppFactory::setContainer($container);
$app = AppFactory::create();

/* ルーティングの設定 */
$app->get("/", App\Controllers\FormController::class . ":request");
$app->post("/", App\Controllers\FormController::class . ":request");
$app->get("/confirm", App\Controllers\FormController::class . ":confirm");
$app->post("/confirm", App\Controllers\FormController::class . ":confirm");
