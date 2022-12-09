<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository;

use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\Exceptions\CommentNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\CommentsRepositoryException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Person\Name;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqliteCommentsRepository implements CommentsRepositoryInterface
{

    public function __construct(
		private PDO $connection,
		private LoggerInterface $logger
	) {
    }


    public function save(Comment $comment): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO comments (uuid, post_uuid, author_uuid, comment_text) 
VALUES (:uuid, :post_uuid, :author_uuid, :comment_text)'
        );

        $statement->execute([
            ':uuid' => (string)$comment->uuid(),
            ':post_uuid' => $comment->getRecensionPost()->uuid(),
            ':author_uuid' => $comment->getAuthor()->uuid(),
            ':comment_text' => $comment->getText()
        ]);

		$this->logger->info("Comment ({$comment->uuid()}) was saved to database");
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     * @throws CommentNotFoundException|PostNotFoundException
     */
    public function get(UUID $uuid): Comment
    {
        $statement = $this->connection->prepare(
            'SELECT 
    				comments.uuid,
    				comments.post_uuid,
    				comments.author_uuid,
    				comments.comment_text,
    				users.username,
    				users.first_name,
    				users.last_name,
    				users.password
    				FROM comments 
    				INNER JOIN users ON users.user_id = comments.author_uuid
    				WHERE uuid = ?'
        );
        $statement->execute([(string)$uuid]);

        return $this->getComment($statement, $uuid);
    }

    /**
     * @throws PostNotFoundException
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     * @throws CommentNotFoundException
     */
    private function getComment(PDOStatement $statement, string $errString ): Comment
	{
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
			$this->logger->warning("Comment ($errString) not found");
            throw new CommentNotFoundException(
                "Cannot find comment: $errString"
            );
        }

//        $userRepository = new SqliteUsersRepository($this->connection);
//        $postsRepository = new SqlitePostsRepository($this->connection);
//        $user = $userRepository->get(new UUID($result['author_uuid']));
//        $post = $postsRepository->get(new UUID($result['post_uuid']));

		$commentAuthor = new User(
			new UUID($result['author_uuid']),
			new Name(
				$result['first_name'],
				$result['last_name'],
			),
			$result['username'],
			$result['password'],
		);

		$postsRepository = new SqlitePostsRepository(
			$this->connection,
				$this->logger);
		$post = $postsRepository->get(new UUID($result['post_uuid']));

        return new Comment(
            new UUID($result['uuid']),
			$commentAuthor,
            $post,
            $result['comment_text']
        );
    }

	/**
	 * @throws CommentsRepositoryException
	 */
	public function delete(UUID $uuid): void
	{
		try {
			$statement = $this->connection->prepare(
				'DELETE FROM comments WHERE uuid = :uuid'
			);
			$statement->execute([':uuid' => $uuid]);

		} catch (\PDOException $exception) {
			throw new CommentsRepositoryException(
				$exception->getMessage(),
				(int)$exception->getCode(),
				$exception
			);
		}

	}
}