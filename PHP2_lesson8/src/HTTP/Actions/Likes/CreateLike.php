<?php

namespace GeekBrains\LevelTwo\HTTP\Actions\Likes;

use GeekBrains\LevelTwo\Blog\Exceptions\AuthException;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Like;
use GeekBrains\LevelTwo\Blog\Repositories\LikesRepository\LikesRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\HTTP\Actions\ActionInterface;
use GeekBrains\LevelTwo\HTTP\Auth\TokenAuthenticationInterface;
use GeekBrains\LevelTwo\HTTP\ErrorResponse;
use GeekBrains\LevelTwo\HTTP\Request;
use GeekBrains\LevelTwo\HTTP\Response;
use GeekBrains\LevelTwo\HTTP\SuccessfulResponse;

class CreateLike implements ActionInterface
{
	public function __construct(
		private TokenAuthenticationInterface $authentication,
		private PostsRepositoryInterface $postsRepository,
		private LikesRepositoryInterface $likesRepository,
	) {
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function handle(Request $request): Response
	{
		try {
			$post_uuid = new UUID($request->jsonBodyField('post_uuid'));
		} catch (HttpException | InvalidArgumentException $exception) {
			return new ErrorResponse($exception->getMessage());
		}

		try {
			$this->postsRepository->get(new UUID($post_uuid));
		} catch (PostNotFoundException $exception) {
			return new ErrorResponse($exception->getMessage());
		}

//		try {
//			$author_uuid = new UUID($request->jsonBodyField('author_uuid'));
//		} catch (HttpException | InvalidArgumentException $exception) {
//			return new ErrorResponse($exception->getMessage());
//		}
//
//		try {
//			$this->usersRepository->get($author_uuid);
//		} catch (UserNotFoundException $exception) {
//			return new ErrorResponse($exception->getMessage());
//		}

		try {
			$author_uuid = $this->authentication->user($request)->uuid();
		} catch (AuthException | UserNotFoundException $exception) {
			return new ErrorResponse($exception->getMessage());
		}

		try {
			$checkLikes = $this->likesRepository->getByPostUuid($post_uuid);
		} catch (\InvalidArgumentException $exception) {
			return new ErrorResponse($exception->getMessage());
		}

		if (array_key_exists((string)$author_uuid, $checkLikes)) {
			return new ErrorResponse(
				'This author has already tag the post'
			);
		}

		$newLikeUuid = UUID::random();

		try {
			$like = new Like(
				uuid: $newLikeUuid,
				post_uuid: $post_uuid,
				author_uuid: $author_uuid,
			);
		} catch (HttpException $exception) {
			return new ErrorResponse($exception->getMessage());
		}

		$this->likesRepository->save($like);

		return new SuccessfulResponse([
			'uuid' => (string)$newLikeUuid,
		]);
	}
}