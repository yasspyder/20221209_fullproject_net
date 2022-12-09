<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\UsersRepository;

use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Person\Name;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;

class DummyUsersRepository implements UsersRepositoryInterface
{

    public function save(User $user): void
    {
        // TODO: Implement save() method.
    }

	/**
	 * @throws UserNotFoundException
	 */
	public function get(UUID $uuid): User
    {
        throw new UserNotFoundException("Not found");
    }

	/**
	 * @throws InvalidArgumentException
	 */
	public function getByUsername(string $username): User
    {
        return new User(UUID::random(), new Name("first", "last"), "user123", "123");
    }

	public function delete(UUID $uuid): void
	{
		// TODO: Implement delete() method.
	}
}