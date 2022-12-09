<?php

namespace GeekBrains\LevelTwo\HTTP\Auth;

use GeekBrains\LevelTwo\Blog\Exceptions\AuthException;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\HTTP\Request;

class PasswordAuthentication implements PasswordAuthenticationInterface
{
	public function __construct(
		private UsersRepositoryInterface $usersRepository
	) {
	}

	/**
	 * @throws AuthException
	 */
	public function user(Request $request): User
	{
		try {
			$username = $request->jsonBodyField('username');
		} catch (HttpException $exception) {
			throw new AuthException($exception->getMessage());
		}
		try {
			$user = $this->usersRepository->getByUsername($username);
		} catch (UserNotFoundException $exception) {
			throw new AuthException($exception->getMessage());
		}

		try {
			$password = $request->jsonBodyField('password');
		} catch (HttpException $exception) {
			throw new AuthException($exception->getMessage());
		}


		if (!$user->checkPassword($password)) {
			throw new AuthException("Wrong password");
		}

		return $user;

	}
}