<?php
namespace App\Controllers;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Views\Twig;
use Slim\Csrf\Guard;
use App\Services\MailerService;
use App\Services\ValidationService;
use App\Models\ErrorInfo;
use App\Models\Contact;

class FormController
{
	protected $view;
	protected $csrf;

	public function __construct(Twig $view, Guard $csrf)
	{
		$this->view = $view;
		$this->csrf = $csrf;
	}

	public function defaultContext($context)
	{
		if (isset($_ENV["IS_DEVELOPMENT"])) {
			$context["isDevelopment"] = $_ENV["IS_DEVELOPMENT"] ?? false;
		} else {
			$context["isDevelopment"] = getenv("IS_DEVELOPMENT") ?? false;
		}

		if (isset($_ENV["DEBUG_MODE"])) {
			$context["isDebugMode"] = $_ENV["DEBUG_MODE"] ?? false;
		} else {
			$context["isDebugMode"] = getenv("DEBUG_MODE") ?? false;
		}

		if (isset($_ENV["PROJECT_DOMAIN"])) {
			$context["projectDomain"] = $_ENV["PROJECT_DOMAIN"];
		} else {
			$context["projectDomain"] = getenv("PROJECT_DOMAIN");
		}

		return $context;
	}

	public function request(Request $request, Response $response)
	{
		// CSRFトークンの情報を取得（hiddenフィールド用）
		$csrfNameKey = $this->csrf->getTokenNameKey();
		$csrfValueKey = $this->csrf->getTokenValueKey();
		$csrfName = $this->csrf->getTokenName();
		$csrfValue = $this->csrf->getTokenValue();

		// 開発環境でのみCSRFトークンのデバッグ情報をログ出力
		$isDevelopment = filter_var($_ENV["DEVELOPMENT_MODE"] ?? false, FILTER_VALIDATE_BOOLEAN);
		if ($isDevelopment) {
			error_log(
				"CSRFトークン情報: NameKey=" .
					$csrfNameKey .
					", ValueKey=" .
					$csrfValueKey .
					", Name=" .
					$csrfName .
					", Value=" .
					$csrfValue
			);
		}

		// セッションからバリデーションエラーと入力データを取得
		$validationErrors = $_SESSION["validation_errors"] ?? [];
		$formData = $_SESSION["form_data"] ?? [];

		// セッションからクリア（一度表示したら削除）
		unset($_SESSION["validation_errors"]);
		unset($_SESSION["form_data"]);

		return $this->view->render(
			$response,
			"Pages/index.twig",
			$this->defaultContext([
				"csrfNameKey" => $csrfNameKey,
				"csrfValueKey" => $csrfValueKey,
				"csrfName" => $csrfName,
				"csrfValue" => $csrfValue,
				"validationErrors" => $validationErrors,
				"formData" => $formData,
			])
		);
	}

	public function confirm(Request $request, Response $response)
	{
		$data = $request->getParsedBody();

		// Contactモデルでデータを扱う
		if (IS_DEVELOPMENT && empty($data)) {
			$contact = Contact::dummy();
		} else {
			$contact = Contact::fromArray($data);
		}

		// バリデーション実行
		list($isValid, $errors) = ValidationService::validateContactForm($contact->toArray());
		if (!$isValid) {
			// セッションにエラー情報と入力データを保存
			$_SESSION["validation_errors"] = $errors;
			$_SESSION["form_data"] = $contact->toArray();

			// 入力画面にリダイレクト
			return $response->withHeader("Location", PROJECT_DOMAIN . "/")->withStatus(302);
		}

		// CSRFトークンの情報を取得（hiddenフィールド用）
		$csrfNameKey = $this->csrf->getTokenNameKey();
		$csrfValueKey = $this->csrf->getTokenValueKey();
		$csrfName = $this->csrf->getTokenName();
		$csrfValue = $this->csrf->getTokenValue();

		// バリデーション成功時
		$_SESSION["form_data"] = $contact->toArray();
		return $this->view->render(
			$response,
			"Pages/confirm.twig",
			$this->defaultContext([
				"csrfNameKey" => $csrfNameKey,
				"csrfValueKey" => $csrfValueKey,
				"csrfName" => $csrfName,
				"csrfValue" => $csrfValue,
				"data" => $contact->toArray(),
			])
		);
	}

	public function error(Request $request, Response $response)
	{
		// セッションからエラー情報を取得
		$errorInfoArray = $_SESSION["error_info"] ?? null;
		$errorInfo = $errorInfoArray ? ErrorInfo::fromArray($errorInfoArray) : null;

		// 本番環境でエラー情報がない場合は入力画面にリダイレクト
		if (!IS_DEVELOPMENT && !$errorInfo) {
			return $response->withHeader("Location", PROJECT_DOMAIN . "/")->withStatus(302);
		}

		// 開発環境ではダミーデータを提供
		if (IS_DEVELOPMENT && !$errorInfo) {
			$errorInfo = ErrorInfo::mockError();
		}

		// エラー情報をクリア（一度表示したら削除）
		unset($_SESSION["error_info"]);

		return $this->view->render(
			$response,
			"Pages/error.twig",
			$this->defaultContext([
				"error_info" => $errorInfo,
			])
		);
	}

	public function send(Request $request, Response $response)
	{
		$data = $_SESSION["form_data"] ?? null;

		if (!$data) {
			return $response->withHeader("Location", PROJECT_DOMAIN . "/")->withStatus(302);
		}

		$mailer = new MailerService();

		// 開発環境でのみSMTP設定をログ出力
		if (IS_DEVELOPMENT) {
			error_log(
				"SMTP設定確認: Host=" .
					($_ENV["SMTP_HOST"] ?? "未設定") .
					", Port=" .
					($_ENV["SMTP_PORT"] ?? "未設定")
			);
		}

		try {
			$mailer->send(
				$_ENV["MAIL_ADMIN"],
				"記事作成代行サービスへのお問い合わせがありました。",
				$this->view->getEnvironment()->render("Mail/admin.twig", [
					"data" => $data,
				])
			);
			$mailer->send(
				$data["email"],
				"【株式会社LiKG】記事作成代行サービスへのお問い合わせありがとうございます。",
				$this->view->getEnvironment()->render("Mail/client.twig", [
					"data" => $data,
				])
			);
		} catch (\Exception $e) {
			// 開発環境でのみ詳細なエラーログを出力
			if (IS_DEVELOPMENT) {
				error_log("メール送信エラー詳細: " . $e->getMessage());
				error_log("エラートレース: " . $e->getTraceAsString());
			}

			// エラー情報をセッションに保存してエラーページにリダイレクト
			$errorInfo = ErrorInfo::mailSendError($e->getMessage(), $data);
			$_SESSION["error_info"] = $errorInfo->toArray();

			return $response->withHeader("Location", PROJECT_DOMAIN . "/error/")->withStatus(302);
		}

		// 成功時
		unset($_SESSION["form_data"]);
		$_SESSION["mail_sent"] = true; // 送信完了フラグを設定
		return $response->withHeader("Location", PROJECT_DOMAIN . "/complete/")->withStatus(302);
	}

	public function complete(Request $request, Response $response)
	{
		// 開発環境ではセッションフラグのチェックをスキップ

		if (!IS_DEVELOPMENT) {
			// 本番環境では送信完了フラグをチェック
			if (!isset($_SESSION["mail_sent"]) || $_SESSION["mail_sent"] !== true) {
				// 送信を経ていない場合は入力画面にリダイレクト
				return $response->withHeader("Location", PROJECT_DOMAIN . "/")->withStatus(302);
			}

			// 送信完了フラグをクリア（一度表示したら削除）
			unset($_SESSION["mail_sent"]);
		}

		return $this->view->render($response, "Pages/complete.twig", $this->defaultContext([]));
	}
}
