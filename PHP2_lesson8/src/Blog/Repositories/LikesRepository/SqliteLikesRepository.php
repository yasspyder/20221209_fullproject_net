<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\LikesRepository;

use GeekBrains\LevelTwo\Blog\Exceptions\LikesNotFoundException;
use GeekBrains\LevelTwo\Blog\Like;
use GeekBrains\LevelTwo\Blog\UUID;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqliteLikesRepository implements LikesRepositoryInterface
{
	public function __construct(
		private PDO $connection,
		private LoggerInterface $logger,
	) {
	}

	public function save(Like $like): void
	{
		$statement = $this->connection->prepare(
			'INSERT INTO likes (uuid, post_uuid, author_uuid)
                 VALUES (:uuid, :post_uuid, :author_uuid)'
		);

		$statement->execute([
			':uuid' => (string)$like->uuid(),
			':post_uuid' => $like->getPostUuid(),
			':author_uuid' => $like->getAuthorUuid(),
		]);

		$this->logger->info("Like ({$like->uuid()}) was saved to database");
	}

	/**
	 * @throws LikesNotFoundException
	 */
	public function getByPostUuid(UUID $post_uuid): array
	{
		$statement = $this->connection->prepare(
			'SELECT * FROM likes WHERE post_uuid = ?'
		);
		$statement->execute([(string)$post_uuid]);

		return $this->getLikes($statement, $post_uuid);
	}

	/**
	 * @throws LikesNotFoundException
	 */
	private function getLikes(PDOStatement $statement, string $errString ): array
	{
		$resultLikes = $statement->fetchAll(PDO::FETCH_ASSOC);

		if ($resultLikes === false) {
			$this->logger->warning("Lake ($errString) not found");
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