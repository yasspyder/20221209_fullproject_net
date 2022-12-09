<?php

namespace GeekBrains\LevelTwo\HTTP\Actions\Auth;

use GeekBrains\LevelTwo\Blog\Exceptions\AuthException;
use GeekBrains\LevelTwo\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use GeekBrains\LevelTwo\HTTP\Actions\ActionInterface;
use GeekBrains\LevelTwo\HTTP\Auth\GetAuthTokenFromHeader;
use GeekBrains\LevelTwo\HTTP\Request;
use GeekBrains\LevelTwo\HTTP\Response;
use GeekBrains\LevelTwo\HTTP\SuccessfulResponse;
use DateTimeImmutable;

class LogOut implements ActionInterface
{
	public function __construct(
		private AuthTokensRepositoryInterface $authTokensRepository,
		private GetAuthTokenFromHeader $authToken
	) {
	}

	/**
	 * @throws AuthException
	 */
	public function handle(Request $request): Response
	{

		$outToken = $this->authToken->getAuthToken($request);

		$token = $outToken->token();
		$outToken->setExpiresOn(new DateTimeImmutable("now"));

		$this->authTokensRepository->save($outToken);

		return new SuccessfulResponse([
			'token' => (string)$token
		]);

	}

}