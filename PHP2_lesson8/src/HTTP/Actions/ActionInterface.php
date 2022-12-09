<?php

namespace GeekBrains\LevelTwo\HTTP\Actions;

use GeekBrains\LevelTwo\HTTP\Request;
use GeekBrains\LevelTwo\HTTP\Response;

interface ActionInterface
{
	public function handle(Request $request): Response;

}