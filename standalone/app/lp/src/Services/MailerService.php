<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerService
{
	public function send($to, $subject, $body)
	{
		$mail = new PHPMailer(true);
		try {
			$mail->isSMTP();
			$mail->isSMTP();
			$mail->Host = $_ENV["SMTP_HOST"];
			$mail->SMTPAuth = filter_var($_ENV["SMTP_AUTH"] ?? false, FILTER_VALIDATE_BOOLEAN);
			switch ($_ENV["SMTP_SECURE"]) {
				case "tls":
					$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
					break;
				case "ssl":
					$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
					break;
				default:
					$mail->SMTPSecure = false;
			}
			$mail->Port = $_ENV["SMTP_PORT"];

			// 認証が有効な場合のみユーザー名とパスワードを設定
			if ($mail->SMTPAuth) {
				$mail->Username = $_ENV["SMTP_USER"];
				$mail->Password = $_ENV["SMTP_PASS"];
			}

			$mail->setFrom($_ENV["MAIL_FROM"], $_ENV["MAIL_FROM_NAME"]);

			// 複数の宛先に対応（より堅牢な実装）
			$recipients = $this->parseRecipients($to);
			if (empty($recipients)) {
				throw new \Exception("有効な宛先が指定されていません");
			}

			foreach ($recipients as $recipient) {
				$mail->addAddress($recipient);
			}

			$mail->isHTML(false);
			$mail->CharSet = "UTF-8";
			$mail->Subject = $subject;
			$mail->Body = $body;

			$mail->send();
			return true;
		} catch (Exception $e) {
			error_log("PHPMailerエラー: " . $e->getMessage());
			error_log(
				"SMTP設定: Host=" .
					$_ENV["SMTP_HOST"] .
					", Port=" .
					$_ENV["SMTP_PORT"] .
					", User=" .
					$_ENV["SMTP_USER"]
			);
			throw new \Exception("メール送信に失敗しました: " . $e->getMessage());
		}
	}

	/**
	 * 宛先を解析して有効なメールアドレスの配列を返す
	 */
	private function parseRecipients($to): array
	{
		$recipients = [];

		if (is_array($to)) {
			$recipients = $to;
		} else {
			// カンマ、セミコロン、改行で区切られた文字列を配列に変換
			$recipients = preg_split('/[,;\n\r]+/', $to);
		}

		// 各宛先をクリーニング
		$validRecipients = [];
		foreach ($recipients as $recipient) {
			$recipient = trim($recipient);
			if (!empty($recipient) && filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
				$validRecipients[] = $recipient;
			}
		}

		return $validRecipients;
	}
}
