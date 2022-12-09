<?php

namespace GeekBrains\LevelTwo\Blog;


class Comment
{
    public function __construct(
        private UUID $uuid,
    	private User $author,
    	private Post $recensionPost,
    	private string $comment_text
    ) {
    }

    public function __toString()
    {
        return $this->author . ' пишет к статье: ' . PHP_EOL .'"'. $this->recensionPost->getText() .'"'. PHP_EOL . " комментарий >>> " . $this->comment_text  . PHP_EOL;
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
    public function setId(UUID $uuid): void
    {
        $this->uuid = $uuid;
    }

    /**
     * @return User
     */
    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * @param User $author
     */
    public function setAuthor(User $author): void
    {
        $this->author = $author;
    }

    /**
     * @return Post
     */
    public function getRecensionPost(): Post
    {
        return $this->recensionPost;
    }

    /**
     * @param Post $recensionPost
     */
    public function setRecensionPost(Post $recensionPost): void
    {
        $this->recensionPost = $recensionPost;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->comment_text;
    }

	/**
	 * @param string $comment_text
	 */
    public function setText(string $comment_text): void
    {
		$this->comment_text = $comment_text;
    }

}