<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\UsersRepository;

use GeekBrains\LevelTwo\Blog\Exceptions\{InvalidArgumentException, UserNotFoundException, UserRepositoryException};
use GeekBrains\LevelTwo\Blog\{User, UUID};
use GeekBrains\LevelTwo\Person\Name;
use \PDO;
use \PDOStatement;
use Psr\Log\LoggerInterface;

class SqliteUsersRepository implements UsersRepositoryInterface
{
    public function __construct(
		private PDO $connection,
		private LoggerInterface $logger
	) {
    }


    public function save(User $user): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO users (
                   user_id, 
                   username, 
                   first_name, 
                   last_name, 
                   password
                   ) VALUES (
                	:uuid, 
                	:username, 
                	:first_name, 
                	:last_name, 
                	:password
                ) 
                ON CONFLICT (user_id) DO UPDATE SET 
                	first_name = :first_name,
                    last_name = :last_name'
        );

        $statement->execute([
            ':uuid' => (string)$user->uuid(),
            ':username' => $user->username(),
            ':first_name' => $user->name()->first(),
            ':last_name' => $user->name()->last(),
			':password' => $user->hashedPassword(),
        ]);

		$this->logger->info("User ({$user->uuid()}) was saved to database");
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function get(UUID $uuid): User
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE user_id = ?'
        );

        $statement->execute([(string)$uuid]);

        return $this->getUser($statement, $uuid);

    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function getByUsername(string $username): User
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE username = :username'
        );

        $statement->execute([
            ':username' => $username,
        ]);

        return $this->getUser($statement, $username);
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    private function getUser(PDOStatement $statement, string $errString): User
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
			$this->logger->warning("User ($errString) not found");
            throw new UserNotFoundException(
                "Cannot find user: $errString"
            );
        }

        return new User(
            new UUID($result['user_id']),
            new Name($result['first_name'], $result['last_name']),
            $result['username'],
			$result['password']
        );
    }


	/**
	 * @throws UserRepositoryException
	 */
	public function delete(UUID $uuid): void
	{
		try {
			$statement = $this->connection->prepare(
				'DELETE FROM users WHERE uuid = :uuid'
			);
			$statement->execute([':uuid' => $uuid]);

		} catch (\PDOException $exception) {
			throw new UserRepositoryException(
				$exception->getMessage(),
				(int)$exception->getCode(),
				$exception
			);
		}

	}
}