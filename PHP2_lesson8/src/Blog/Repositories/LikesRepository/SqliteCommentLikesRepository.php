<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\LikesRepository;

use GeekBrains\LevelTwo\Blog\CommentsLike;
use GeekBrains\LevelTwo\Blog\Exceptions\LikesNotFoundException;
use GeekBrains\LevelTwo\Blog\UUID;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqliteCommentLikesRepository implements CommentLikesRepositoryInterface
{
	public function __construct(
		private PDO $connection,
		private LoggerInterface $logger,
	) {
	}

	public function save(CommentsLike $like): void
	{
		$statement = $this->connection->prepare(
			'INSERT INTO commentLikes (uuid, comment_uuid, author_uuid)
                 VALUES (:uuid, :comment_uuid, :author_uuid)'
		);

		$statement->execute([
			':uuid' => (string)$like->uuid(),
			':comment_uuid' => $like->getCommentUuid(),
			':author_uuid' => $like->getAuthorUuid(),
		]);

		$this->logger->info("Like ({$like->uuid()}) was saved to database");
	}

	/**
	 * @throws LikesNotFoundException
	 */
	public function getByCommentUuid(UUID $comment_uuid): array
	{
		$statement = $this->connection->prepare(
			'SELECT * FROM commentLikes WHERE comment_uuid = ?'
		);
		$statement->execute([(string)$comment_uuid]);

		return $this->getLikes($statement, $comment_uuid);
	}

	/**
	 * @throws LikesNotFoundException
	 */
	private function getLikes(PDOStatement $statement, string $errString ): array
	{
		$resultLikes = $statement->fetchAll(PDO::FETCH_ASSOC);

		if ($resultLikes === false) {
			$this->logger->warning("Like ($errString) not found");
			throw new LikesNotFoundException(
				"Cannot find likes: $errString"
			);
		}

		$result = [];

		foreach ($resultLikes as $like) {
			$result[$like['author_uuid']] = $like['uuid'];
		}

		return $result;
	}

}