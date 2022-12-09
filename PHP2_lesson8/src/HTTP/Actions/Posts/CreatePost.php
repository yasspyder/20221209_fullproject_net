<?php

namespace GeekBrains\LevelTwo\HTTP\Actions\Posts;

use GeekBrains\LevelTwo\Blog\Exceptions\AuthException;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\HTTP\Actions\ActionInterface;
use GeekBrains\LevelTwo\HTTP\Auth\AuthenticationInterface;
use GeekBrains\LevelTwo\HTTP\Auth\TokenAuthenticationInterface;
use GeekBrains\LevelTwo\HTTP\ErrorResponse;
use GeekBrains\LevelTwo\HTTP\Request;
use GeekBrains\LevelTwo\HTTP\Response;
use GeekBrains\LevelTwo\HTTP\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class CreatePost implements ActionInterface
{
	public function __construct(
		private TokenAuthenticationInterface $authentication,
		private PostsRepositoryInterface $postsRepository,
		private LoggerInterface $logger
	) {
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws AuthException
	 * @throws UserNotFoundException
	 */
	public function handle(Request $request): Response
	{
		try {
			$author = $this->authentication->user($request);
		} catch (AuthException | UserNotFoundException $exception) {
			return new ErrorResponse($exception->getMessage());
		}


		$newPostUuid = UUID::random();

		try {
			$post = new Post(
				$newPostUuid,
				$author,
				$request->jsonBodyField('post_title'),
				$request->jsonBodyField('post_text')
			);
		} catch (HttpException $exception) {
			return new ErrorResponse($exception->getMessage());
		}

		$this->postsRepository->save($post);
		$this->logger->info("Post created: $newPostUuid");

		return new SuccessfulResponse([
			'uuid' => (string)$newPostUuid
		]);
	}
}