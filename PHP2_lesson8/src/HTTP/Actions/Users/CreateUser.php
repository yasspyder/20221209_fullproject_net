<?php

namespace GeekBrains\LevelTwo\HTTP\Actions\Users;

use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\HTTP\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\HTTP\Request;
use GeekBrains\LevelTwo\HTTP\Response;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use GeekBrains\LevelTwo\Person\Name;

class CreateUser implements ActionInterface
{

	public function __construct(
		private UsersRepositoryInterface $usersRepository,
	) {
	}

	public function handle(Request $request): Response
	{
		try {
//			$newUserUuid = UUID::random();

//			$user = new User(
//				$newUserUuid,
//				new Name(
//					$request->jsonBodyField('first_name'),
//					$request->jsonBodyField('last_name')
//				),
//				$request->jsonBodyField('username'),
//				$request->jsonBodyField('password')
//			);

			$user = User::creatFrom(
				new Name(
					$request->jsonBodyField('first_name'),
					$request->jsonBodyField('last_name')
				),
				$request->jsonBodyField('username'),
				$request->jsonBodyField('password')
			);

		} catch (HttpException | InvalidArgumentException $exception) {
			return new ErrorResponse($exception->getMessage());
		}

		$this->usersRepository->save($user);

		return new SuccessfulResponse([
			'uuid' => (string)$user->uuid(),
		]);
	}
}