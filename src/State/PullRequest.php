<?php

declare(strict_types=1);

namespace CIInfo\State;

use CIInfo\State;

/**
 * The state representing a pull request build.
 */
class PullRequest implements State
{
    /**
     * The name of the target branch.
     *
     * @var string
     */
    private $targetBranch;

    /**
     * The SHA-1 of the base commit (that is, the last commit of the base branch).
     *
     * @var string
     */
    private $baseSha1;

    /**
     * The SHA-1 of the head commit (that is, the last commit of the pull request branch).
     *
     * @var string
     */
    private $headSha1;

    /**
     * The SHA-1 of the merge commit.
     *
     * @var string
     */
    private $mergeSha1;

    /**
     * @param string $baseSha1 the SHA-1 of the base commit (that is, the last commit of the base branch)
     * @param string $headSha1 the SHA-1 of the head commit (that is, the last commit of the pull request branch)
     * @param string $mergeSha1 the SHA-1 of the merge commit
     */
    public function __construct(string $targetBranch, string $baseSha1, string $headSha1, string $mergeSha1)
    {
        $this->targetBranch = $targetBranch;
        $this->baseSha1 = $baseSha1;
        $this->headSha1 = $headSha1;
        $this->mergeSha1 = $mergeSha1;
    }

    /**
     * {@inheritdoc}
     *
     * @see \CIInfo\State::getEvent()
     */
    public function getEvent(): string
    {
        return State::EVENT_PULLREQUEST;
    }

    /**
     * Get the name of the target branch.
     */
    public function getTargetBranch(): string
    {
        return $this->targetBranch;
    }

    /**
     * {@inheritdoc}
     *
     * @see \CIInfo\State::getLastCommitSha1()
     */
    public function getLastCommitSha1(): string
    {
        return $this->getMergeSha1();
    }

    /**
     * Get the SHA-1 of the base commit (that is, the last commit of the base branch).
     */
    public function getBaseSha1(): string
    {
        return $this->baseSha1;
    }

    /**
     * Get the SHA-1 of the head commit (that is, the last commit of the pull request branch).
     */
    public function getHeadSha1(): string
    {
        return $this->headSha1;
    }

    /**
     * Get the SHA-1 of the merge commit.
     */
    public function getMergeSha1(): string
    {
        return $this->mergeSha1;
    }

    /**
     * Get the pull request commit range.
     */
    public function getCommitRange(): string
    {
        return "{$this->getBaseSha1()}...{$this->getHeadSha1()}";
    }
}
