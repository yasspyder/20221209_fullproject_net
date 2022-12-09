<?php

namespace GeekBrains\LevelTwo\HTTP\Auth;

use GeekBrains\LevelTwo\Blog\Exceptions\AuthException;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\HTTP\Request;

class JsonBodyUsernameIdentification implements IdentificationInterface
{
	public function __construct(
		private UsersRepositoryInterface $usersRepository
	) {
	}

	public function user(Request $request): User
	{
		try {
			$username = $request->jsonBodyField('username');
		} catch (HttpException | InvalidArgumentException $exception) {
			throw new AuthException($exception->getMessage());
		}

		try {
			return $this->usersRepository->getByUsername($username);
		} catch (UserNotFoundException $exception) {
			throw new AuthException($exception->getMessage());
		}
	}
}