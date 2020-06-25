<?php

declare(strict_types=1);

namespace CIInfo\State;

use CIInfo\State;

class Tag implements State
{
    /**
     * The commit SHA-1.
     *
     * @var string
     */
    private $lastCommitSha1;

    /**
     * The tag.
     *
     * @var string
     */
    private $tag;

    /**
     * @param string $sha1 the commit SHA-1
     * @param string $tag the tag
     */
    public function __construct(string $lastCommitSha1, string $tag)
    {
        $this->lastCommitSha1 = $lastCommitSha1;
        $this->tag = $tag;
    }

    /**
     * {@inheritdoc}
     *
     * @see \CIInfo\State::getEvent()
     */
    public function getEvent(): string
    {
        return State::EVENT_TAG;
    }

    /**
     * {@inheritdoc}
     *
     * @see \CIInfo\State::getLastCommitSha1()
     */
    public function getLastCommitSha1(): string
    {
        return $this->lastCommitSha1;
    }

    /**
     * Get the tag.
     */
    public function getTag(): string
    {
        return $this->tag;
    }
}
