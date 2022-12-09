<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\LikesRepository;

use GeekBrains\LevelTwo\Blog\CommentsLike;
use GeekBrains\LevelTwo\Blog\UUID;

interface CommentLikesRepositoryInterface
{
	public function save(CommentsLike $like): void;
	public function getByCommentUuid(UUID $comment_uuid): array;

}