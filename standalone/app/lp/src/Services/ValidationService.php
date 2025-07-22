<?php
namespace App\Services;

use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

class ValidationService
{
	public static function validateContactForm(array $data)
	{
		$validator = v::key(
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
				"message",
				v::optional(
					v::length(0, 1000)->setTemplate("お問い合わせ内容は1000文字以下で入力してください")
				)
			)
			->key("privacy_policy", v::notEmpty()->setTemplate("プライバシーポリシーに同意してください"));

		try {
			$validator->assert($data);
			return [true, []];
		} catch (NestedValidationException $e) {
			return [false, $e->getMessages()];
		}
	}
}
