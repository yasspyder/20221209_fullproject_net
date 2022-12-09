<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\AuthTokensRepository;

use GeekBrains\LevelTwo\Blog\AuthToken;
use GeekBrains\LevelTwo\Blog\Exceptions\AuthTokensRepositoryException;
use GeekBrains\LevelTwo\Blog\UUID;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqliteAuthTokensRepository implements AuthTokensRepositoryInterface
{
	public function __construct(
		private PDO $connection,
		private LoggerInterface $logger
	) {
	}

	/**
	 * @throws AuthTokensRepositoryException
	 */
	public function save(AuthToken $authToken): void
	{
		$query = <<< 'SQL'
		INSERT INTO tokens (
		        token,
		        user_uuid,
		        expires_on
		) VALUES (
		          :token,
		          :user_uuid,
		          :expires_on
		)
		ON CONFLICT (token) DO UPDATE SET
				expires_on = :expires_on
SQL;

		try {
			$statement = $this->connection->prepare($query);
			$statement->execute([
				':token' => (string)$authToken->token(),
				':user_uuid' => (string)$authToken->userUuid(),
				':expires_on' => $authToken->expiresOn()
					->format(\DateTimeInterface::ATOM)
			]);
		} catch (\PDOException $exception) {
			throw new AuthTokensRepositoryException(
				$exception->getMessage(), (int)$exception->getCode(), $exception
			);
		}

		$this->logger->info("Token ({$authToken->token()}) was saved to database");
	}

	/**
	 * @throws AuthTokensRepositoryException
	 */
	public function get(string $token): AuthToken
	{
		try {
			$statement = $this->connection->prepare(
				'SELECT * FROM tokens WHERE token = ?'
			);
			$statement->execute([$token]);
			$result = $statement->fetch(PDO::FETCH_ASSOC);
		} catch (\PDOException $exception) {
			throw new AuthTokensRepositoryException(
				$exception->getMessage(), (int)$exception->getCode(), $exception
			);
		}

		if ($result === false) {
			$this->logger->warning("Token ($token) not found");
			throw new AuthTokensRepositoryException(
				"Cannot find token: $token"
			);
		}

		try {
			return new AuthToken(
				$result['token'],
				new UUID($result['user_uuid']),
				new \DateTimeImmutable($result['expires_on'])
			);
		} catch (\Exception $exception) {
			throw new AuthTokensRepositoryException(
				$exception->getMessage(), $exception->getCode(), $exception
			);
		}

	}

}