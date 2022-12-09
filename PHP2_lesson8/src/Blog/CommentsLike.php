<?php

namespace GeekBrains\LevelTwo\Blog;

class CommentsLike
{
	public function __construct(
		private UUID $uuid,
		private UUID $comment_uuid,
		private UUID $author_uuid,
	) {
	}

	/**
	 * @return UUID
	 */
	public function uuid(): UUID
	{
		return $this->uuid;
	}

	/**
	 * @param UUID $uuid
	 */
	public function setUuid(UUID $uuid): void
	{
		$this->uuid = $uuid;
	}

	public function getCommentUuid(): string
	{
		return $this->comment_uuid;
	}

	public function getAuthorUuid(): string
	{
		return $this->author_uuid;
	}

	/**
	 * @param UUID $author_uuid
	 */
	public function setAuthorUuid(UUID $author_uuid): void
	{
		$this->author_uuid = $author_uuid;
	}

	/**
	 * @param UUID $comment_uuid
	 */
	public function setCommentUuid(UUID $comment_uuid): void
	{
		$this->comment_uuid = $comment_uuid;
	}


}