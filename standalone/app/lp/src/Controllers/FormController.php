<?php
namespace App\Controllers;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Views\Twig;
use Slim\Csrf\Guard;
use App\Modules\Mailer;
use App\Modules\ErrorInfo;
use Respect\Validation\Validator as v;

class FormController
{
	protected $view;
	protected $csrf;

	public function __construct(Twig $view, Guard $csrf)
	{
		$this->view = $view;
		$this->csrf = $csrf;
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

		return $this->view->render($response, "Pages/article-writing-service/request/request.twig", [
			"csrfNameKey" => $csrfNameKey,
			"csrfValueKey" => $csrfValueKey,
			"csrfName" => $csrfName,
			"csrfValue" => $csrfValue,
			"validationErrors" => $validationErrors,
			"formData" => $formData,
		]);
	}

	public function confirm(Request $request, Response $response)
	{
		$data = $request->getParsedBody();

		// 開発環境ではダミーデータを提供
		$isDevelopment = filter_var($_ENV["DEVELOPMENT_MODE"] ?? false, FILTER_VALIDATE_BOOLEAN);
		if ($isDevelopment && empty($data)) {
			$data = [
				"company" => "テスト株式会社",
				"name" => "テスト太郎",
				"email" => "test@example.com",
				"tel" => "09012345678",
				"candidate_date_1" => "2025年7月15日 14:00",
				"candidate_date_2" => "2025年7月16日 10:00",
				"candidate_date_3" => "2025年7月17日 16:00",
				"keyword" => "SEO対策,コンテンツマーケティング",
				"message" =>
					"記事作成代行サービスについて詳しく知りたいです。現在のWebサイトの課題や目標についても相談させていただきたいと思います。",
				"privacy_policy" => "1",
			];
		}

		// バリデーションルールの定義
		$validator = v::key(
			"company",
			v::notEmpty()
				->setTemplate("御社名は必須項目です")
				->length(1, 100)
				->setTemplate("御社名は1文字以上100文字以下で入力してください")
		)
			->key(
				"name",
				v::notEmpty()
					->setTemplate("ご担当者名は必須項目です")
					->length(1, 50)
					->setTemplate("ご担当者名は1文字以上50文字以下で入力してください")
			)
			->key(
				"email",
				v::notEmpty()
					->setTemplate("メールアドレスは必須項目です")
					->email()
					->setTemplate("メールアドレスの形式が正しくありません")
			)
			->key(
				"tel",
				v::optional(
					v::regex('/^\+?[0-9]{8,15}$/')->setTemplate(
						"電話番号は8桁から15桁の数字で入力してください（ハイフンなし）"
					)
				)
			)
			->key(
				"candidate_date_1",
				v::optional(
					v::length(1, 50)->setTemplate(
						"面談候補日時 第一希望は1文字以上50文字以下で入力してください"
					)
				)
			)
			->key(
				"candidate_date_2",
				v::optional(
					v::length(1, 50)->setTemplate(
						"面談候補日時 第二希望は1文字以上50文字以下で入力してください"
					)
				)
			)
			->key(
				"candidate_date_3",
				v::optional(
					v::length(1, 50)->setTemplate(
						"面談候補日時 第三希望は1文字以上50文字以下で入力してください"
					)
				)
			)
			->key(
				"keyword",
				v::optional(
					v::length(1, 100)->setTemplate(
						"メイン対策キーワードは1文字以上100文字以下で入力してください"
					)
				)
			)
			->key(
				"message",
				v::optional(
					v::length(0, 1000)->setTemplate("お問い合わせ内容は1000文字以下で入力してください")
				)
			)
			->key("privacy_policy", v::notEmpty()->setTemplate("プライバシーポリシーに同意してください"));

		// バリデーション実行
		try {
			$validator->assert($data);
		} catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
			$errors = [];
			foreach ($e->getMessages() as $message) {
				$errors[] = $message;
			}

			// セッションにエラー情報と入力データを保存
			$_SESSION["validation_errors"] = $errors;
			$_SESSION["form_data"] = $data;

			// 入力画面にリダイレクト
			return $response
				->withHeader("Location", $_ENV["DOMAIN"] . "/article-writing-service/request/")
				->withStatus(302);
		} catch (\Exception $e) {
			// その他のエラー
			$isDevelopment = filter_var($_ENV["DEVELOPMENT_MODE"] ?? false, FILTER_VALIDATE_BOOLEAN);
			if ($isDevelopment) {
				error_log("予期しないエラー発生: " . $e->getMessage());
				error_log("エラートレース: " . $e->getTraceAsString());
			}

			$_SESSION["validation_errors"] = ["システムエラーが発生しました"];
			$_SESSION["form_data"] = $data;

			return $response
				->withHeader("Location", $_ENV["DOMAIN"] . "/article-writing-service/request/")
				->withStatus(302);
		}

		// CSRFトークンの情報を取得（hiddenフィールド用）
		$csrfNameKey = $this->csrf->getTokenNameKey();
		$csrfValueKey = $this->csrf->getTokenValueKey();
		$csrfName = $this->csrf->getTokenName();
		$csrfValue = $this->csrf->getTokenValue();

		// バリデーション成功時
		$_SESSION["form_data"] = $data;
		return $this->view->render(
			$response,
			"Pages/article-writing-service/request/confirm/confirm.twig",
			[
				"csrfNameKey" => $csrfNameKey,
				"csrfValueKey" => $csrfValueKey,
				"csrfName" => $csrfName,
				"csrfValue" => $csrfValue,
				"data" => $data,
			]
		);
	}

	public function error(Request $request, Response $response)
	{
		// 開発環境ではセッションフラグのチェックをスキップ
		$isDevelopment = filter_var($_ENV["DEVELOPMENT_MODE"] ?? false, FILTER_VALIDATE_BOOLEAN);

		// セッションからエラー情報を取得
		$errorInfoArray = $_SESSION["error_info"] ?? null;
		$errorInfo = $errorInfoArray ? ErrorInfo::fromArray($errorInfoArray) : null;

		// 本番環境でエラー情報がない場合は入力画面にリダイレクト
		if (!$isDevelopment && !$errorInfo) {
			return $response
				->withHeader("Location", $_ENV["DOMAIN"] . "/article-writing-service/request/")
				->withStatus(302);
		}

		// 開発環境ではダミーデータを提供
		if ($isDevelopment && !$errorInfo) {
			$errorInfo = ErrorInfo::mockError();
		}

		// エラー情報をクリア（一度表示したら削除）
		unset($_SESSION["error_info"]);

		return $this->view->render(
			$response,
			"Pages/article-writing-service/request/error/error.twig",
			[
				"error_info" => $errorInfo,
				"isDevelopment" => $isDevelopment,
			]
		);
	}

	public function send(Request $request, Response $response)
	{
		$data = $_SESSION["form_data"] ?? null;

		if (!$data) {
			return $response
				->withHeader("Location", $_ENV["DOMAIN"] . "/article-writing-service/request/")
				->withStatus(302);
		}

		$mailer = new Mailer();

		// 開発環境でのみSMTP設定をログ出力
		$isDevelopment = filter_var($_ENV["DEVELOPMENT_MODE"] ?? false, FILTER_VALIDATE_BOOLEAN);
		if ($isDevelopment) {
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
				$this->view->getEnvironment()->render("Mail/article-writing-service/admin.twig", [
					"data" => $data,
				])
			);
			$mailer->send(
				$data["email"],
				"【株式会社LiKG】記事作成代行サービスへのお問い合わせありがとうございます。",
				$this->view->getEnvironment()->render("Mail/article-writing-service/client.twig", [
					"data" => $data,
				])
			);
		} catch (\Exception $e) {
			// 開発環境でのみ詳細なエラーログを出力
			$isDevelopment = filter_var($_ENV["DEVELOPMENT_MODE"] ?? false, FILTER_VALIDATE_BOOLEAN);
			if ($isDevelopment) {
				error_log("メール送信エラー詳細: " . $e->getMessage());
				error_log("エラートレース: " . $e->getTraceAsString());
			}

			// エラー情報をセッションに保存してエラーページにリダイレクト
			$errorInfo = ErrorInfo::mailSendError($e->getMessage(), $data);
			$_SESSION["error_info"] = $errorInfo->toArray();

			return $response
				->withHeader("Location", $_ENV["DOMAIN"] . "/article-writing-service/request/error/")
				->withStatus(302);
		}

		// 成功時
		unset($_SESSION["form_data"]);
		$_SESSION["mail_sent"] = true; // 送信完了フラグを設定
		return $response
			->withHeader("Location", $_ENV["DOMAIN"] . "/article-writing-service/request/complete/")
			->withStatus(302);
	}

	public function complete(Request $request, Response $response)
	{
		// 開発環境ではセッションフラグのチェックをスキップ
		$isDevelopment = filter_var($_ENV["DEVELOPMENT_MODE"] ?? false, FILTER_VALIDATE_BOOLEAN);

		if (!$isDevelopment) {
			// 本番環境では送信完了フラグをチェック
			if (!isset($_SESSION["mail_sent"]) || $_SESSION["mail_sent"] !== true) {
				// 送信を経ていない場合は入力画面にリダイレクト
				return $response
					->withHeader("Location", $_ENV["DOMAIN"] . "/article-writing-service/request/")
					->withStatus(302);
			}

			// 送信完了フラグをクリア（一度表示したら削除）
			unset($_SESSION["mail_sent"]);
		}

		return $this->view->render(
			$response,
			"Pages/article-writing-service/request/complete/complete.twig"
		);
	}
}
