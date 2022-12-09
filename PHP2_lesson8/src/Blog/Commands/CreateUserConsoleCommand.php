<?php

namespace GeekBrains\LevelTwo\Blog\Commands;

use GeekBrains\LevelTwo\Blog\Exceptions\ArgumentsException;
use GeekBrains\LevelTwo\Blog\Exceptions\CommandException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Person\Name;
use Psr\Log\LoggerInterface;


class CreateUserConsoleCommand
{

// Команда зависит от контракта репозитория пользователей,
// а не от конкретной реализации
    public function __construct(
        private UsersRepositoryInterface $usersRepository,
		private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws CommandException
     * @throws InvalidArgumentException|ArgumentsException
     */
    public function handle(Arguments $arguments): void
    {
		$this->logger->info("Create user command started");

        $username = $arguments->get('username');
		$name = new Name(
			$arguments->get('first_name'),
			$arguments->get('last_name')
		);
		$password = $arguments->get('password');
//		$userUuid = UUID::random();
//		$hash = hash('sha256', $password.$userUuid);

// Проверяем, существует ли пользователь в репозитории
        if ($this->userExists($username)) {
// Бросаем исключение, если пользователь уже существует
            $this->logger->warning("User already exists: $username");
			throw new CommandException("User already exists: $username");
        }
        // Сохраняем пользователя в репозиторий
//        $this->usersRepository->save(new User(
//            uuid: $userUuid,
//            name: new Name(
//                $arguments->get('first_name'),
//                $arguments->get('last_name')),
//            username: $username,
//			hashedPassword: $hash,
//        ));

		$user = User::creatFrom(
			$name,
			$username,
			$password
		);

		$this->usersRepository->save($user);
		$this->logger->info("User created: uuid");
    }
    private function userExists(string $username): bool
    {
        try {
        // Пытаемся получить пользователя из репозитория
            $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException) {
            return false;
        }
        return true;
    }

}