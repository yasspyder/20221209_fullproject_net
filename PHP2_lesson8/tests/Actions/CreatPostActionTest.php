<?php

namespace GeekBrains\PHPUnit\Actions;

use GeekBrains\LevelTwo\Blog\Exceptions\JsonException;
use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\AuthException;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Http\Actions\Posts\CreatePost;
use GeekBrains\LevelTwo\HTTP\Auth\IdentificationInterface;
use GeekBrains\LevelTwo\HTTP\Auth\JsonBodyUuidIdentification;
use GeekBrains\LevelTwo\HTTP\Auth\TokenAuthenticationInterface;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use GeekBrains\LevelTwo\Person\Name;
use GeekBrains\PHPUnit\DummyLogger;
use PHPUnit\Framework\TestCase;

class CreatPostActionTest extends TestCase
{
	private function identification($userRepository): TokenAuthenticationInterface
	{
		return new class($userRepository) implements TokenAuthenticationInterface
		{
			public function __construct(
				private UsersRepositoryInterface $usersRepository
			) {
			}

			public function user(Request $request): User
			{
				$userUuid = new UUID($request->jsonBodyField('author_uuid'));
				return $this->usersRepository->get($userUuid);
			}
		};
	}

	private function usersRepository(array $users): UsersRepositoryInterface
	{
		return new class($users) implements UsersRepositoryInterface
		{
			public function __construct(
				private array $users
			)
			{
			}

			public function save(User $user): void
			{
			}

			public function get(UUID $uuid): User
			{
				foreach ($this->users as $user) {
					if ($user instanceof User && (string)$uuid == $user->uuid()) {
						return $user;
					}
				}

				throw new UserNotFoundException('Cannot find user: ' . $uuid);
			}

			public function getByUsername(string $username): User
			{
				throw new UserNotFoundException('Not found');
			}

			public function delete(UUID $uuid): void
			{
				// TODO: Implement delete() method.
			}
		};
	}

	private function postsRepository(): PostsRepositoryInterface
	{
		return new class() implements PostsRepositoryInterface {
			private bool $called = false;

			public function __construct()
			{
			}

			public function save(Post $post): void
			{
				$this->called = true;
			}

			public function get(UUID $uuid): Post
			{
				throw new PostNotFoundException('Not found');
			}

			public function getByTitle(string $title): Post
			{
				throw new PostNotFoundException('Not found');
			}

			public function getCalled(): bool
			{
				return $this->called;
			}

			public function delete(UUID $uuid): void
			{
			}
		};
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @throws InvalidArgumentException
	 * @throws \JsonException
	 */
	public function testItReturnsSuccessfulResponse(): void
	{
		$request = new Request([], [], '{"author_uuid":"10373537-0805-4d7a-830e-22b481b4859c","post_title":"post_title","post_text":"post_text"}');

		$postsRepository = $this->postsRepository();

		$usersRepository = $this->usersRepository([
			new User(
				new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
				new Name('name', 'surname'),
				'username',
				'123'
			),
		]);

		$identification = $this->identification($usersRepository);

		$action = new CreatePost(
			$identification,
			$postsRepository,
			new DummyLogger());

		$response = $action->handle($request);

		$this->assertInstanceOf(SuccessfulResponse::class, $response);

		$this->setOutputCallback(function ($data){
			$dataDecode = json_decode(
				$data,
				associative: true,
				flags: JSON_THROW_ON_ERROR
			);

			$dataDecode['data']['uuid'] = "351739ab-fc33-49ae-a62d-b606b7038c87";
			return json_encode(
				$dataDecode,
				JSON_THROW_ON_ERROR
			);
		});

		$this->expectOutputString('{"success":true,"data":{"uuid":"351739ab-fc33-49ae-a62d-b606b7038c87"}}');


		$response->send();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @throws \JsonException|InvalidArgumentException
	 */
	public function testItReturnsErrorResponseIfNotFoundUser(): void
	{
		$request = new Request([], [], '{"author_uuid":"10373537-0805-4d7a-830e-22b481b4859c","post_title":"post_title","post_text":"post_text"}');

		$postsRepository = $this->postsRepository();
		$usersRepository = $this->usersRepository([]);

		$identification = $this->identification($usersRepository);

		$action = new CreatePost(
			$identification,
			$postsRepository,
			new DummyLogger()
		);

		$response = $action->handle($request);

		$this->assertInstanceOf(ErrorResponse::class, $response);
		$this->expectOutputString('{"success":false,"reason":"Cannot find user: 10373537-0805-4d7a-830e-22b481b4859c"}');

		$response->send();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @throws InvalidArgumentException
	 * @throws \JsonException
	 */
	public function testItReturnsErrorResponseIfNoTextProvided(): void
	{
		$request = new Request([], [], '{"author_uuid":"10373537-0805-4d7a-830e-22b481b4859c","post_title":"post_title"}');

		$postsRepository = $this->postsRepository([]);
		$usersRepository = $this->usersRepository([
			new User(
				new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
				new Name('Ivan', 'Nikitin'),
				'ivan',
				'123'
			),
		]);

		$identification = $this->identification($usersRepository);

		$action = new CreatePost(
			$identification,
			$postsRepository,
			new DummyLogger()
		);

		$response = $action->handle($request);

		$this->assertInstanceOf(ErrorResponse::class, $response);
		$this->expectOutputString('{"success":false,"reason":"No such field: post_text"}');

		$response->send();
	}

}