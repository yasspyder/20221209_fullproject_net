<?php

namespace GeekBrains\LevelTwo\HTTP;

use JetBrains\PhpStorm\ArrayShape;

class ErrorResponse extends Response
{
	protected const SUCCESS = false;

	public function __construct(
		private string $reason = "Something goes wrong",
	) {
	}

	#[ArrayShape(['reason' => "string"])]
	protected function payload(): array
	{
		return ['reason' => $this->reason];
	}
}