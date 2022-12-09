<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\PostsRepository;

use GeekBrains\LevelTwo\Person\Name;
use GeekBrains\LevelTwo\Blog\{Post, Repositories\UsersRepository\SqliteUsersRepository, User, UUID};
use GeekBrains\LevelTwo\Blog\Exceptions\{InvalidArgumentException,
	PostNotFoundException,
	PostRepositoryException,
	UserNotFoundException};
use Psr\Log\LoggerInterface;
use PDO;
use PDOStatement;

class SqlitePostsRepository implements PostsRepositoryInterface
{

    public function __construct(
		private PDO $connection,
		private LoggerInterface $logger,
	) {
    }


    public function save(Post $post): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO posts (uuid, author_uuid, post_title, post_text) 
VALUES (:uuid, :author_uuid, :post_title, :post_text)'
        );

        $statement->execute([
            ':uuid' => (string)$post->uuid(),
            ':author_uuid' => $post->getAuthor()->uuid(),
            ':post_title' => $post->getPostHeader(),
            ':post_text' => $post->getText()
        ]);

		$this->logger->info("Post ({$post->uuid()}) was saved to database");
    }

    /**
     * @throws PostNotFoundException
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function get(UUID $uuid): Post
    {
        $statement = $this->connection->prepare(
            'SELECT 
    				posts.uuid,
    				posts.author_uuid,
    				posts.post_title,
    				posts.post_text,
    				users.username, 
    				users.first_name, 
    				users.last_name,
    				users.password
    				FROM posts INNER JOIN users ON users.user_id = posts.author_uuid
    				    WHERE uuid = ?'
        );
        $statement->execute([(string)$uuid]);

        return $this->getPost($statement, $uuid);
    }

    /**
     * @throws PostNotFoundException
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    private function getPost(PDOStatement $statement, string $errString,): Post
    {

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
			$this->logger->warning("Post ($errString) not found");
            throw new PostNotFoundException(
                "Cannot find post: $errString"
            );
        }

        //$userRepository = new SqliteUsersRepository($this->connection);
        //$user = $userRepository->get(new UUID($result['author_uuid']));

        return new Post(
            new UUID($result['uuid']),
            new User(
				new UUID($result['author_uuid']),
				new Name(
					$result['first_name'],
					$result['last_name'],
				),
				$result['username'],
				$result['password']
			),
            $result['post_title'],
            $result['post_text'],
        );
    }


	/**
	 * @throws PostRepositoryException
	 */
	public function delete(UUID $uuid): void
	{
		try {
			$statement = $this->connection->prepare(
				'DELETE FROM posts WHERE uuid = :uuid'
			);

			$statement->execute([':uuid' => $uuid]);

		} catch (\PDOException $exception) {
			throw new PostRepositoryException(
				$exception->getMessage(),
				(int)$exception->getCode(),
				$exception
			);
		}

	}
}