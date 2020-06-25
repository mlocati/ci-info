<?php

declare(strict_types=1);

namespace CIInfo\State;

use CIInfo\State;

/**
 * The state representing a manually-triggered build (via APIs, web requests, ...).
 */
class Manual implements State
{
    /**
     * The name of the built branch.
     *
     * @var string
     */
    private $branch;

    /**
     * The commit SHA-1.
     *
     * @var string
     */
    private $lastCommitSha1;

    /**
     * @param string $branch the name of the built branch
     * @param string $lastCommitSha1 the commit SHA-1
     */
    public function __construct(string $branch, string $lastCommitSha1)
    {
        $this->branch = $branch;
        $this->lastCommitSha1 = $lastCommitSha1;
    }

    /**
     * {@inheritdoc}
     *
     * @see \CIInfo\State::getEvent()
     */
    public function getEvent(): string
    {
        return State::EVENT_MANUAL;
    }

    /**
     * Get the name of the built branch.
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
}
