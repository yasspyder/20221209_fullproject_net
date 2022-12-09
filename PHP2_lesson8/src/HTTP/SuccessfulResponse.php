<?php declare(strict_types=1);

namespace GeekBrains\LevelTwo\HTTP;

class SuccessfulResponse extends Response
{
	protected const SUCCESS = true;

	public function __construct(
		private array $data = []
	) {
	}

	protected function payload(): array
	{
		return ['data' => $this->data];
	}
}