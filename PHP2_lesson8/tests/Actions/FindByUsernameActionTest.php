<?php

namespace GeekBrains\PHPUnit\Actions;

use GeekBrains\LevelTwo\Blog\Exceptions\{UserNotFoundException, InvalidArgumentException, JsonException};
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\HTTP\Actions\Users\FindByUsername;
use GeekBrains\LevelTwo\HTTP\ErrorResponse;
use GeekBrains\LevelTwo\HTTP\Request;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use GeekBrains\LevelTwo\Person\Name;
use PHPUnit\Framework\TestCase;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;

class FindByUsernameActionTest extends TestCase
{
	private function usersRepository(array $users): UsersRepositoryInterface
	{
		return new class($users) implements UsersRepositoryInterface {

			public function __construct(
				private array $users
			)
			{
			}

			public function save(User $user): void
			{
				// TODO: Implement save() method.
			}

			public function get(UUID $uuid): User
			{
				throw new UserNotFoundException("Not found");
			}

			public function getByUsername(string $username): User
			{
				foreach ($this->users as $user) {
					if ($user instanceof User && $username === $user->username()) {
						return $user;
					}
				}

				throw new UserNotFoundException("Not found");
			}

			public function delete(UUID $uuid): void
			{
				// TODO: Implement delete() method.
			}
		};
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @throws \JsonException
	 */
	public function testItReturnsErrorResponseIfNoUsernameProvided(): void
	{
		$request = new Request([],[],'');
		$usersRepository = $this->usersRepository([]);
		$action = new FindByUsername($usersRepository);
		$response = $action->handle($request);

		$this->assertInstanceOf(ErrorResponse::class, $response);
		$this->expectOutputString('{"success":false,"reason":"No such query param in the request: username"}');

		$response->send();

	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @throws \JsonException
	 */
	public function testItReturnsErrorResponseIfUserNotFound(): void
	{
		$request = new Request(['username' => 'ivan'], [], '');
		$usersRepository = $this->usersRepository([]);
		$action = new FindByUsername($usersRepository);
		$response = $action->handle($request);
		$this->assertInstanceOf(ErrorResponse::class, $response);
		$this->expectOutputString('{"success":false,"reason":"Not found"}');
		$response->send();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @throws InvalidArgumentException|\JsonException
	 */

	public function testItReturnsSuccessfulResponse(): void

	{
		$request = new Request(['username' => 'ivan'], [], '');

		$usersRepository = $this->usersRepository([
			new User(
				UUID::random(),
				new Name('Ivan', 'Nikitin'),
				'ivan',
					'123'
			),
		]);
		$action = new FindByUsername($usersRepository);
		$response = $action->handle($request);
		$this->assertInstanceOf(SuccessfulResponse::class, $response);
		$this->expectOutputString('{"success":true,"data":{"username":"ivan","name":"Ivan Nikitin"}}');
		$response->send();
	}

}