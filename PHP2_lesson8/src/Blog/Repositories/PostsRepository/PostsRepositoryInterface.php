<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\PostsRepository;


use GeekBrains\LevelTwo\Blog\{Post, UUID};

interface PostsRepositoryInterface
{

    public function save(Post $post): void;
    public function get(UUID $uuid): Post;
	public function delete(UUID $uuid): void;

}