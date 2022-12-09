<?php

namespace GeekBrains\LevelTwo\HTTP\Auth;

use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\HTTP\Request;

interface AuthenticationInterface
{
	public function user(Request $request): User;

}