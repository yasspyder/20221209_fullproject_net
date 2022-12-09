<?php

namespace GeekBrains\LevelTwo\HTTP\Actions\Likes;

use GeekBrains\LevelTwo\Blog\CommentsLike;
use GeekBrains\LevelTwo\Blog\Exceptions\AuthException;
use GeekBrains\LevelTwo\Blog\Exceptions\CommentNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Like;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\LikesRepository\CommentLikesRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\HTTP\Actions\ActionInterface;
use GeekBrains\LevelTwo\HTTP\Auth\TokenAuthenticationInterface;
use GeekBrains\LevelTwo\HTTP\ErrorResponse;
use GeekBrains\LevelTwo\HTTP\Request;
use GeekBrains\LevelTwo\HTTP\Response;
use GeekBrains\LevelTwo\HTTP\SuccessfulResponse;

class CreateCommentLike implements ActionInterface
{
	public function __construct(
		private TokenAuthenticationInterface $authentication,
		private CommentsRepositoryInterface $commentsRepository,
		private CommentLikesRepositoryInterface $commentLikesRepository,
	) {
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function handle(Request $request): Response
	{
		try {
			$comment_uuid = new UUID($request->jsonBodyField('comment_uuid'));
		} catch (HttpException | InvalidArgumentException $exception) {
			return new ErrorResponse($exception->getMessage());
		}

		try {
			$this->commentsRepository->get($comment_uuid);
		} catch (CommentNotFoundException $exception) {
			return new ErrorResponse($exception->getMessage());
		}

		try {
			$author_uuid = $this->authentication->user($request)->uuid();
		} catch (AuthException | UserNotFoundException $exception) {
			return new ErrorResponse($exception->getMessage());
		}

		try {
			$checkLikes = $this->commentLikesRepository->getByCommentUuid($comment_uuid);
		} catch (InvalidArgumentException $exception) {
			return new ErrorResponse($exception->getMessage());
		}

		if (array_key_exists((string)$author_uuid, $checkLikes)) {
			return new ErrorResponse(
				'This author has already tag the comment'
			);
		}

		$newLikeUuid = UUID::random();

		try {
			$commentLike = new CommentsLike(
				uuid: $newLikeUuid,
				comment_uuid: $comment_uuid,
				author_uuid: $author_uuid,
			);
		} catch (HttpException $exception) {
			return new ErrorResponse($exception->getMessage());
		}

		$this->commentLikesRepository->save($commentLike);

		return new SuccessfulResponse([
			'uuid' => (string)$newLikeUuid,
		]);
	}

}