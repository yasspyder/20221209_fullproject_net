<?php

namespace GeekBrains\LevelTwo\HTTP\Auth;

use GeekBrains\LevelTwo\Blog\Exceptions\AuthException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\HTTP\Request;
use DateTimeImmutable;

class BearerTokenAuthentication implements TokenAuthenticationInterface
{
	public function __construct(
		private GetAuthTokenFromHeader $authTokenFromHeader,
		private UsersRepositoryInterface $usersRepository
	) {
	}

	/**
	 * @throws AuthException
	 */
	public function user(Request $request): User
	{
		$authToken = $this->authTokenFromHeader->getAuthToken($request);

		$token = $authToken->token();

		if ($authToken->expiresOn() <= new DateTimeImmutable()) {
			throw new AuthException("Token expired: [$token]");
		}

		$userUuid = $authToken->userUuid();

		return $this->usersRepository->get($userUuid);

	}
}