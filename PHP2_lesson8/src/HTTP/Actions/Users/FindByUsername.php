<?php

namespace GeekBrains\LevelTwo\HTTP\Actions\Users;

use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\HTTP\Actions\ActionInterface;
use GeekBrains\LevelTwo\HTTP\ErrorResponse;
use GeekBrains\LevelTwo\HTTP\Request;
use GeekBrains\LevelTwo\HTTP\Response;
use GeekBrains\LevelTwo\HTTP\SuccessfulResponse;

class FindByUsername implements ActionInterface
{
	public function __construct(
		private UsersRepositoryInterface $usersRepository
	) {
	}

	public function handle(Request $request): Response
	{
		try {
			$username = $request->query('username');
		} catch (HttpException $exception) {
			return new ErrorResponse($exception->getMessage());
		}

		try {
			$user = $this->usersRepository->getByUsername($username);
		} catch (UserNotFoundException $exception) {
			return new ErrorResponse($exception->getMessage());
		}

		return new SuccessfulResponse([
			'username' => $user->username(),
			'name' => $user->name()->first() . ' ' . $user->name()->last()
		]);
	}
}