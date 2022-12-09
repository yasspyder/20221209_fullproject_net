<?php

namespace GeekBrains\LevelTwo\HTTP\Actions\Comments;

use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\HTTP\Auth\TokenAuthenticationInterface;
use GeekBrains\LevelTwo\Blog\Exceptions\{AuthException,
	HttpException,
	InvalidArgumentException,
	PostNotFoundException,
	UserNotFoundException};
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\HTTP\Actions\ActionInterface;
use GeekBrains\LevelTwo\HTTP\ErrorResponse;
use GeekBrains\LevelTwo\HTTP\Request;
use GeekBrains\LevelTwo\HTTP\Response;
use GeekBrains\LevelTwo\HTTP\SuccessfulResponse;

class CreateComment implements ActionInterface
{
	public function __construct(
		private TokenAuthenticationInterface $authentication,
		private PostsRepositoryInterface $postsRepository,
		private CommentsRepositoryInterface $commentsRepository,
	) {
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function handle(Request $request): Response
	{
//		try {
//			$author_uuid = new UUID($request->jsonBodyField('author_uuid'));
//		} catch (HttpException | InvalidArgumentException $exception) {
//			return new ErrorResponse($exception->getMessage());
//		}
//
//		try {
//			$user = $this->usersRepository->get($author_uuid);
//		} catch (UserNotFoundException $exception) {
//			return new ErrorResponse($exception->getMessage());
//		}

		try {
			$user = $this->authentication->user($request);
		} catch (AuthException | UserNotFoundException $exception) {
			return new ErrorResponse($exception->getMessage());
		}

		try {
			$post_uuid = new UUID($request->jsonBodyField('post_uuid'));
		} catch (HttpException | InvalidArgumentException $exception) {
			return new ErrorResponse($exception->getMessage());
		}

		try {
			$post = $this->postsRepository->get($post_uuid);
		} catch (PostNotFoundException $exception) {
			return new ErrorResponse($exception->getMessage());
		}

		$newCommentUuid = UUID::random();

		try {
			$comment = new Comment(
				$newCommentUuid,
				$user,
				$post,
				$request->jsonBodyField('comment_text')
			);
		} catch (HttpException $exception) {
			return new ErrorResponse($exception->getMessage());
		}

		$this->commentsRepository->save($comment);

		return new SuccessfulResponse([
			'uuid' => (string)$newCommentUuid
		]);
	}
}