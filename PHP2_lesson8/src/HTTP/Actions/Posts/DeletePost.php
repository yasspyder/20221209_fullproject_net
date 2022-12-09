<?php

namespace GeekBrains\LevelTwo\HTTP\Actions\Posts;

use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\HTTP\Actions\ActionInterface;
use GeekBrains\LevelTwo\HTTP\ErrorResponse;
use GeekBrains\LevelTwo\HTTP\Request;
use GeekBrains\LevelTwo\HTTP\Response;
use GeekBrains\LevelTwo\HTTP\SuccessfulResponse;

class DeletePost implements ActionInterface
{
	public function __construct(
		private PostsRepositoryInterface $postsRepository
	) {
	}

	/**
	 * @throws HttpException
	 * @throws InvalidArgumentException
	 */
	public function handle(Request $request): Response
	{
		try {
			$postUuid = $request->query('post_uuid');
			$this->postsRepository->get(new UUID($postUuid));
		} catch (PostNotFoundException $exception) {
			return new ErrorResponse($exception->getMessage());
		}

		$this->postsRepository->delete(new UUID($postUuid));

		return new SuccessfulResponse([
			'post_uuid' => $postUuid
		]);
	}
}