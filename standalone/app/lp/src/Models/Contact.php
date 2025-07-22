<?php
namespace App\Models;

class Contact
{
	public $name;
	public $email;
	public $tel;
	public $message;
	public $privacy_policy;

	public function __construct(
		$name = "",
		$email = "",
		$tel = "",
		$message = "",
		$privacy_policy = ""
	) {
		$this->name = $name;
		$this->email = $email;
		$this->tel = $tel;
		$this->message = $message;
		$this->privacy_policy = $privacy_policy;
	}

	/**
	 * 配列からContactインスタンスを生成
	 * @param array $data
	 * @return self
	 */
	public static function fromArray(array $data): self
	{
		return new self(
			$data["name"] ?? "",
			$data["email"] ?? "",
			$data["tel"] ?? "",
			$data["message"] ?? "",
			$data["privacy_policy"] ?? ""
		);
	}

	/**
	 * Contactインスタンスを配列に変換
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			"name" => $this->name,
			"email" => $this->email,
			"tel" => $this->tel,
			"message" => $this->message,
			"privacy_policy" => $this->privacy_policy,
		];
	}

	/**
	 * ダミーデータ生成
	 * @return self
	 */
	public static function dummy(): self
	{
		return new self(
			"試験 太郎",
			"test@example.com",
			"09012345678",
			"これはお問合せのテストです。\nこれは、お問合せの、テストです。",
			"1"
		);
	}
}
