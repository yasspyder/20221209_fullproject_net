<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository;

use GeekBrains\LevelTwo\Blog\{Comment, UUID};

interface CommentsRepositoryInterface
{

    public function save(Comment $comment): void;
    public function get(UUID $uuid): Comment;
	public function delete(UUID $uuid): void;

}