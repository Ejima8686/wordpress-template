<?php

namespace App\Models;

class ErrorInfo
{
	public function __construct(
		public readonly string $type,
		public readonly string $message,
		public readonly int $timestamp,
		public readonly ?string $errorDetail = null,
		public readonly ?array $data = null
	) {}

	/**
	 * セッションに保存するための配列に変換
	 */
	public function toArray(): array
	{
		return [
			"type" => $this->type,
			"message" => $this->message,
			"error_detail" => $this->errorDetail,
			"data" => $this->data,
			"timestamp" => $this->timestamp,
		];
	}

	/**
	 * セッション配列からErrorInfoオブジェクトを作成
	 */
	public static function fromArray(array $array): self
	{
		return new self(
			type: $array["type"] ?? "",
			message: $array["message"] ?? "",
			timestamp: $array["timestamp"] ?? time(),
			errorDetail: $array["error_detail"] ?? null,
			data: $array["data"] ?? null
		);
	}

	/**
	 * メール送信エラーを作成
	 */
	public static function mailSendError(string $errorDetail, ?array $data = null): self
	{
		$errorInfo = new self(
			type: "mail_send_error",
			message: "メール送信中にエラーが発生しました。",
			timestamp: time(),
			errorDetail: $errorDetail,
			data: $data
		);

		// エラーログを出力（個人情報は含めない）
		$ipAddress = $_SERVER["REMOTE_ADDR"] ?? "unknown";
		error_log(
			"メール送信エラー: " .
				$errorDetail .
				" | IP: " .
				$ipAddress .
				" | Time: " .
				date("Y-m-d H:i:s")
		);

		return $errorInfo;
	}

	/**
	 * CSRFエラーを作成
	 */
	public static function csrfError(): self
	{
		$errorInfo = new self(
			type: "csrf_failure",
			message: "セキュリティのため、ページを再読み込みしてから再度お試しください。",
			timestamp: time(),
			errorDetail: null,
			data: null
		);

		// 開発環境でのみエラーログを出力
		$isDevelopment = filter_var($_ENV["DEVELOPMENT_MODE"] ?? false, FILTER_VALIDATE_BOOLEAN);
		if ($isDevelopment) {
			$ipAddress = $_SERVER["REMOTE_ADDR"] ?? "unknown";
			error_log(
				"CSRFエラー: トークン検証に失敗しました | IP: " .
					$ipAddress .
					" | Time: " .
					date("Y-m-d H:i:s")
			);
		}

		return $errorInfo;
	}

	/**
	 * バリデーションエラーを作成
	 */
	public static function validationError(array $errors, ?array $data = null): self
	{
		$errorInfo = new self(
			type: "validation_error",
			message: "入力内容に誤りがあります。",
			timestamp: time(),
			errorDetail: implode("\n", $errors),
			data: $data
		);

		// 開発環境でのみエラーログを出力
		$isDevelopment = filter_var($_ENV["DEVELOPMENT_MODE"] ?? false, FILTER_VALIDATE_BOOLEAN);
		if ($isDevelopment) {
			$ipAddress = $_SERVER["REMOTE_ADDR"] ?? "unknown";
			error_log(
				"バリデーションエラー: " .
					implode(", ", $errors) .
					" | IP: " .
					$ipAddress .
					" | Time: " .
					date("Y-m-d H:i:s")
			);
		}

		return $errorInfo;
	}

	/**
	 * システムエラーを作成
	 */
	public static function systemError(string $errorDetail, ?array $data = null): self
	{
		$errorInfo = new self(
			type: "system_error",
			message: "システムエラーが発生しました。",
			timestamp: time(),
			errorDetail: $errorDetail,
			data: $data
		);

		// エラーログを出力（個人情報は含めない）
		$ipAddress = $_SERVER["REMOTE_ADDR"] ?? "unknown";
		error_log(
			"システムエラー: " . $errorDetail . " | IP: " . $ipAddress . " | Time: " . date("Y-m-d H:i:s")
		);

		return $errorInfo;
	}

	/**
	 * モックエラーを作成（開発環境でのテスト用）
	 */
	public static function mockError(): self
	{
		return new self(
			type: "mock_error",
			message: "これは開発環境でのテスト用エラーメッセージです。",
			timestamp: time(),
			errorDetail: "詳細なエラー情報\n複数行のエラーメッセージ\n技術的な詳細情報\nデバッグ用の追加情報",
			data: [
				"company" => "テスト株式会社",
				"name" => "テスト太郎",
				"email" => "test@example.com",
				"tel" => "09012345678",
				"candidate_date_1" => "2025年7月15日 14:00",
				"candidate_date_2" => "2025年7月16日 10:00",
				"candidate_date_3" => "2025年7月17日 16:00",
				"keyword" => "SEO対策,コンテンツマーケティング,Webマーケティング",
				"message" =>
					"記事作成代行サービスについて詳しく知りたいです。現在のWebサイトの課題や目標についても相談させていただきたいと思います。長文のテストメッセージです。",
				"privacy_policy" => "1",
				"additional_info" => "追加のテスト情報",
				"test_flag" => true,
				"test_number" => 12345,
				"test_array" => ["item1", "item2", "item3"],
				"test_object" => [
					"nested_key" => "nested_value",
					"another_nested" => [
						"deep_key" => "deep_value",
					],
				],
			]
		);
	}
}
