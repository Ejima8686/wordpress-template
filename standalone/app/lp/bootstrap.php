<?php
session_start();
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
 * 定数の定義
 */
define("IS_DEVELOPMENT", getenv("IS_DEVELOPMENT") ?: $_ENV["IS_DEVELOPMENT"] ?? false);
define("DEBUG_MODE", getenv("DEBUG_MODE") ?: $_ENV["DEBUG_MODE"] ?? false);
define("ERROR_LOG_PATH", getenv("ERROR_LOG_PATH") ?: $_ENV["ERROR_LOG_PATH"] ?? "");
define("PROJECT_DOMAIN", getenv("PROJECT_DOMAIN") ?: $_ENV["PROJECT_DOMAIN"] ?? "");

/*
 * デバッグモードの設定
 */
if (DEBUG_MODE) {
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
} else {
	ini_set("display_errors", 0);
}

/**
 * エラー出力先の設定
 */
if (ERROR_LOG_PATH !== "" && file_exists(ERROR_LOG_PATH)) {
	ini_set("error_log", ERROR_LOG_PATH);
	ini_set("log_errors", 1);
} else {
	/* エラー出力先がない場合は強制終了 */
	echo "Error log path not found. Please set ERROR_LOG_PATH in .env file.";
	exit(1);
}

/* プロジェクトドメインの設定 */
if (PROJECT_DOMAIN == "") {
	/* プロジェクトドメインがない場合は強制終了 */
	echo "Project domain not found. Please set PROJECT_DOMAIN in .env file.";
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
		filter_var(IS_DEVELOPMENT, FILTER_VALIDATE_BOOLEAN) &&
		filter_var(DEBUG_MODE, FILTER_VALIDATE_BOOLEAN);
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
			return $response->withHeader("Location", PROJECT_DOMAIN . "/error/")->withStatus(302);
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

/* エラー出力の設定 */
if (DEBUG_MODE) {
	$app->addErrorMiddleware(true, true, true);
} else {
	$app->addErrorMiddleware(false, false, false);
}

// CSRFミドルウェアを追加
$app->add($container->get(Guard::class));

/* ルーティングの設定 */
require_once __DIR__ . "/src/Routes/web.php";
require_once __DIR__ . "/src/Routes/api.php";

// 404エラーハンドラーを追加
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType("application/json");

// 404エラーハンドラー
$errorMiddleware->setErrorHandler(\Slim\Exception\HttpNotFoundException::class, function (
	\Psr\Http\Message\ServerRequestInterface $request,
	\Throwable $exception,
	bool $displayErrorDetails
) use ($container) {
	$returnJson =
		filter_var(IS_DEVELOPMENT, FILTER_VALIDATE_BOOLEAN) &&
		filter_var(DEBUG_MODE, FILTER_VALIDATE_BOOLEAN);
	if ($returnJson) {
		// 開発環境: 詳細なエラー情報をJSONで返す
		$response = new \Slim\Psr7\Response();
		$response->getBody()->write(
			json_encode([
				"error" => "Not Found",
				"message" => "The requested resource was not found",
				"path" => $request->getUri()->getPath(),
				"development" => true,
			])
		);

		return $response->withStatus(404)->withHeader("Content-Type", "application/json");
	} else {
		// 本番環境: シンプルな404ページを表示
		$response = new \Slim\Psr7\Response();
		$html = '<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - ページが見つかりません</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #333; }
        p { color: #666; }
        a { color: #007cba; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>404 - ページが見つかりません</h1>
    <p>お探しのページは存在しないか、移動または削除された可能性があります。</p>
    <p><a href="/article-writing-service/">トップページに戻る</a></p>
</body>
</html>';

		$response->getBody()->write($html);
		return $response->withStatus(404)->withHeader("Content-Type", "text/html; charset=utf-8");
	}
});

$app->run();
