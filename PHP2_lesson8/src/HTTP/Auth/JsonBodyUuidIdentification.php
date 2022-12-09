<?php

namespace GeekBrains\LevelTwo\HTTP\Auth;

use GeekBrains\LevelTwo\Blog\Exceptions\{
	UserNotFoundException,
	InvalidArgumentException,
	AuthException,
	HttpException
};
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\{User, UUID};
use GeekBrains\LevelTwo\HTTP\Request;

class JsonBodyUuidIdentification implements IdentificationInterface
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
			$userUuid = new UUID($request->jsonBodyField('author_uuid'));
		} catch (HttpException | InvalidArgumentException $exception) {
			throw new AuthException($exception->getMessage());
		}

		try {
			return $this->usersRepository->get($userUuid);
		} catch (UserNotFoundException $exception) {
			throw new AuthException($exception->getMessage());
		}
	}
}