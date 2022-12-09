<?php

namespace GeekBrains\LevelTwo\Blog;

class Like
{
	public function __construct(
		private UUID $uuid,
		private UUID $post_uuid,
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

	/**
	 * @return UUID
	 */
	public function getPostUuid(): string
	{
		return $this->post_uuid;
	}

	public function setPostUuid(UUID $post_uuid): void
	{
		$this->post_uuid = $post_uuid;
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





}