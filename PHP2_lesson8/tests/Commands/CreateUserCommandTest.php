<?php

namespace GeekBrains\PHPUnit\Commands;

use GeekBrains\LevelTwo\Blog\Commands\Arguments;
use GeekBrains\LevelTwo\Blog\Commands\CreateUserConsoleCommand;
use GeekBrains\LevelTwo\Blog\Commands\User\CreatUserCommand;
use GeekBrains\LevelTwo\Blog\Exceptions\ArgumentsException;
use GeekBrains\LevelTwo\Blog\Exceptions\CommandException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\DummyUsersRepository;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\PHPUnit\DummyLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\{
	RuntimeException,
	ExceptionInterface
};
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class CreateUserCommandTest extends TestCase
{
	/**
	 * @throws ExceptionInterface
	 */
	public function testItFromConsoleRequiresLastName(): void
	{
		$command = new CreatUserCommand(
			$this->makeUsersRepository()
		);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage(
			'Not enough arguments (missing: "last_name").'
		);

		$command->run(
			new ArrayInput([
				"first_name" => "Vladimir",
				"username" => "user222",
				"password" => "123"
					]),
			new NullOutput()
		);
	}

	/**
	 * @throws ExceptionInterface
	 */
	public function testItFromConsoleRequiresPassword(): void
	{
		$command = new CreatUserCommand(
			$this->makeUsersRepository()
		);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage(
			'Not enough arguments (missing: "password").'
		);

		$command->run(
			new ArrayInput([
				"first_name" => "Vladimir",
				"last_name" => "Ivanov",
				"username" => "user222"
			]),
			new NullOutput()
		);
	}

	/**
	 * @throws ExceptionInterface
	 */
	public function testItFromConsoleRequiresFirstName(): void
	{
		$command = new CreatUserCommand(
			$this->makeUsersRepository()
		);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage(
			'Not enough arguments (missing: "first_name").'
		);

		$command->run(
			new ArrayInput([
				"last_name" => "Ivanov",
				"username" => "user222",
				"password" => "123"
			]),
			new NullOutput()
		);
	}

	/**
	 * @throws ExceptionInterface
	 */
	public function testItFromConsoleSavesUserToRepository(): void
	{
		$usersRepository = new class implements UsersRepositoryInterface {

			private bool $called = false;

			public function save(User $user): void
			{
				$this->called = true;
			}

			public function get(UUID $uuid): User
			{
				throw new UserNotFoundException("User not found");
			}

			public function getByUsername(string $username): User
			{
				throw new UserNotFoundException("User not found");
			}

			public function delete(UUID $uuid): void
			{
				// TODO: Implement delete() method.
			}

			public function wasCalled(): bool
			{
				return $this->called;
			}

		};
		$command = new CreatUserCommand($usersRepository);

		$command->run(
			new ArrayInput([
				"first_name" => "Vladimir",
				"last_name" => "Ivanov",
				"username" => "user222",
				"password" => "123"
			]),
			new NullOutput()
		);

		$this->assertTrue($usersRepository->wasCalled());
	}

	/**
	 * @throws ArgumentsException
	 * @throws InvalidArgumentException
	 */
	public function testItThrowsAnExceptionWhenUserAlreadyExists(): void
    {
        $command = new CreateUserConsoleCommand(
			new DummyUsersRepository(),
			new DummyLogger()
		);
        // Описываем тип ожидаемого исключения
        $this->expectException(CommandException::class);

        // и его сообщение
        $this->expectExceptionMessage('User already exists: Ivan');

        // Запускаем команду с аргументами
        $command->handle(new Arguments([
			'username' => 'Ivan',
			'first_name' => 'Ivan',
			'last_name' => 'Nikitin',
			'password' => '123'
			]));
    }

    // Тест проверяет, что команда действительно требует имя пользователя

	/**
	 * @throws CommandException
	 * @throws InvalidArgumentException
	 */
	public function testItRequiresFirstName(): void
    {
// $usersRepository - это объект анонимного класса,
// реализующего контракт UsersRepositoryInterface
        $usersRepository = new class implements UsersRepositoryInterface {
            public function save(User $user): void
            {
// Ничего не делаем
            }

            public function get(UUID $uuid): User
            {
// И здесь ничего не делаем
                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User
            {

                // И здесь ничего не делаем
                throw new UserNotFoundException("Not found");
            }

			public function delete(UUID $uuid): void
			{
				// TODO: Implement delete() method.
			}
		};
// Передаём объект анонимного класса
// в качестве реализации UsersRepositoryInterface
        $command = new CreateUserConsoleCommand(
			$usersRepository,
			new DummyLogger()
		);
// Ожидаем, что будет брошено исключение
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: first_name');
// Запускаем команду
        $command->handle(new Arguments(['username' => 'Ivan']));
    }


    // Тест проверяет, что команда действительно требует фамилию пользователя

	/**
	 * @throws CommandException
	 * @throws InvalidArgumentException
	 */
	public function testItRequiresLastName(): void
    {
// Передаём в конструктор команды объект, возвращаемый нашей функцией
        $command = new CreateUserConsoleCommand(
			$this->makeUsersRepository(),
			new DummyLogger()
		);
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: last_name');
        $command->handle(new Arguments([
            'username' => 'Ivan',
// Нам нужно передать имя пользователя,
// чтобы дойти до проверки наличия фамилии
            'first_name' => 'Ivan',
        ]));
    }


    // Функция возвращает объект типа UsersRepositoryInterface
    private function makeUsersRepository(): UsersRepositoryInterface
    {
        return new class implements UsersRepositoryInterface {
            public function save(User $user): void
            {
            }

            public function get(UUID $uuid): User
            {
                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User

            {
                throw new UserNotFoundException("Not found");
            }

			public function delete(UUID $uuid): void
			{
				// TODO: Implement delete() method.
			}
		};
    }

    // Тест, проверяющий, что команда сохраняет пользователя в репозитории

	/**
	 * @throws ArgumentsException
	 * @throws CommandException
	 * @throws InvalidArgumentException
	 */
	public function testItSavesUserToRepository(): void
    {
        // Создаём объект анонимного класса
        $usersRepository = new class implements UsersRepositoryInterface {
// В этом свойстве мы храним информацию о том,
// был ли вызван метод save
            private bool $called = false;

            public function save(User $user): void
            {
// Запоминаем, что метод save был вызван
                $this->called = true;
            }

            public function get(UUID $uuid): User
            {

                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException("Not found");
            }
// Этого метода нет в контракте UsersRepositoryInterface,
// но ничто не мешает его добавить.
// С помощью этого метода мы можем узнать,
// был ли вызван метод save
            public function wasCalled(): bool
            {
                return $this->called;
            }

			public function delete(UUID $uuid): void
			{
				// TODO: Implement delete() method.
			}
		};

        $command = new CreateUserConsoleCommand(
			$usersRepository,
			new DummyLogger()
		);

        // Запускаем команду
        $command->handle(new Arguments([
            'username' => 'Ivan',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
			'password' => 'some_password'
        ]));

        $this->assertTrue($usersRepository->wasCalled());
    }

	/**
	 * @throws CommandException
	 * @throws InvalidArgumentException
	 */
	public function  testItRequiresPassword(): void
	{
		$command = new CreateUserConsoleCommand(
			$this->makeUsersRepository(),
			new DummyLogger()
			);

		$this->expectException(ArgumentsException::class);
		$this->expectExceptionMessage("No such argument: password");

		$command->handle(new Arguments([
			'username' => 'Ivan',
			'first_name' => 'Ivan',
			'last_name' => 'Nikitin',
		]));
	}

}