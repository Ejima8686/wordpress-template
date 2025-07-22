<?php

namespace App\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
	private bool $isDevelopment;
	private bool $isDebugMode;

	public function __construct()
	{
		$this->isDevelopment = $_ENV["IS_DEVELOPMENT"] ?? false;
		$this->isDebugMode = $_ENV["DEBUG_MODE"] ?? false;
	}

	public function getFunctions(): array
	{
		return [
			new TwigFunction("format_date", [$this, "formatDate"]),
			new TwigFunction("is_development", [$this, "isDevelopment"]),
			new TwigFunction("is_debug_mode", [$this, "isDebugMode"]),
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

	public function formatDate(string $date, string $format = "Y-m-d H:i:s"): string
	{
		return date($format, strtotime($date));
	}
}
