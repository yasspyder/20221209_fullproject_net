<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\UsersRepository;

use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\{User, UUID};
use Psr\Log\LoggerInterface;

class InMemoryUsersRepository implements UsersRepositoryInterface
{

    private array $users = [];
	private LoggerInterface $logger;


    public function save(User $user): void
    {
        $this->users[] = $user;
		$this->logger->info("User ({$user->uuid()}) was saved to memory");
    }

    /**
     * @param UUID $uuid
     * @return User
     * @throws UserNotFoundException
     */
    public function get(UUID $uuid): User
    {
        foreach ($this->users as $user) {
            if ((string)$user->uuid() === (string)$uuid) {
                return $user;
            }
        }
		$this->logger->warning("User ($uuid) has not found");
        throw new UserNotFoundException("User not found: $uuid");
    }

    /**
     *@throws UserNotFoundException
     */
    public function getByUsername(string $username): User
    {
        foreach ($this->users as $user) {
            if ($user->username() === $username) {
                return $user;
            }
        }
		$this->logger->warning("User ($username) has not found");
        throw new UserNotFoundException("User not found: $username");
    }

	/**
	 * @throws UserNotFoundException
	 */
	public function delete(UUID $uuid): void
	{
		$i = 0;
		foreach ($this->users as $user) {
			if ((string)$user->uuid() === (string)$uuid) {
				unset($user);
				$i++;
			}
		}
		if (!$i) {
			throw new UserNotFoundException("User not found: $uuid");
		}
	}
}