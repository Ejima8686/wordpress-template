<?php

namespace App\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
	private bool $isDevelopment;
	private bool $isDebugMode;
	private string $projectName;

	public function __construct()
	{
		if (isset($_ENV["IS_DEVELOPMENT"])) {
			$this->isDevelopment = $_ENV["IS_DEVELOPMENT"] ?? false;
		} else {
			$this->isDevelopment = getenv("IS_DEVELOPMENT") ?? false;
		}

		if (isset($_ENV["DEBUG_MODE"])) {
			$this->isDebugMode = $_ENV["DEBUG_MODE"] ?? false;
		} else {
			$this->isDebugMode = getenv("DEBUG_MODE") ?? false;
		}
	}

	public function getFunctions(): array
	{
		return [
			new TwigFunction("format_date", [$this, "formatDate"]),
			new TwigFunction("is_development", [$this, "isDevelopment"]),
			new TwigFunction("is_debug_mode", [$this, "isDebugMode"]),
			new TwigFunction("asset", [$this, "asset"]),
			new TwigFunction("public", [$this, "public"]),
		];
	}

	public function isDevelopment(): bool
	{
		return $this->isDevelopment;
	}

	public function isDebugMode(): bool
	{
		return $this->isDebugMode;
	}

	public function projectDomain(): string
	{
		return $this->projectDomain;
	}

	public function formatDate(string $date, string $format = "Y-m-d H:i:s"): string
	{
		return date($format, strtotime($date));
	}

	public function asset(string $path): string
	{
		if ($this->isDevelopment) {
			return "/$src/" . ltrim($path, "/");
		} else {
			return $this->projectDomain . "/dist/" . ltrim($path, "/");
		}
	}

	public function public(string $path): string
	{
		if ($this->isDevelopment) {
			// 開発環境ではViteのpublicフォルダから画像を取得
			return "/" . ltrim($path, "/");
		} else {
			$fullPath = $this->projectDomain . "/dist/" . ltrim($path, "/");
			// 本番環境ではビルドされた画像を取得
			return $fullPath;
		}
	}
}
