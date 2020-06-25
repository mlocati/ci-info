<?php

declare(strict_types=1);

namespace CIInfo\State;

use CIInfo\State;

/**
 * The state representing a push build.
 */
class Push implements State
{
    /**
     * The name of the branch affected by the push.
     *
     * @var string
     */
    private $branch;

    /**
     * The SHA-1 of the head commit (that is, the last commit of the push).
     *
     * @var string
     */
    private $lastCommitSha1;

    /**
     * The SHA-1 of the previous commit (that is, the last commit before the push).
     *
     * @var string
     */
    private $baseCommitSha1;

    /**
     * @param string $branch the name of the branch affected by the push
     * @param string $lastCommitSha1 the SHA-1 of the head commit (that is, the last commit of the push)
     * @param string $baseCommitSha1 the SHA-1 of the previous commit (that is, the last commit before the push)
     */
    public function __construct(string $branch, string $lastCommitSha1, string $baseCommitSha1)
    {
        $this->branch = $branch;
        $this->lastCommitSha1 = $lastCommitSha1;
        $this->baseCommitSha1 = $baseCommitSha1;
    }

    /**
     * {@inheritdoc}
     *
     * @see \CIInfo\State::getEvent()
     */
    public function getEvent(): string
    {
        return State::EVENT_PUSH;
    }

    /**
     * Get the name of the branch affected by the push.
     */
    public function getBranch(): string
    {
        return $this->branch;
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
     * Get the SHA-1 of the base commit (that is, the last commit of the base branch).
     */
    public function getBaseCommitSha1(): string
    {
        return $this->baseCommitSha1;
    }

    /**
     * Get the pull request commit range.
     */
    public function getCommitRange(): string
    {
        return "{$this->getBaseCommitSha1()}...{$this->getLastCommitSha1()}";
    }
}
