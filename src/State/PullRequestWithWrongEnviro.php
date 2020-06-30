<?php

declare(strict_types=1);

namespace CIInfo\State;

/**
 * The state representing a pull request build, where the current environment variables are wrong.
 *
 * @see https://travis-ci.community/t/travis-commit-is-not-the-commit-initially-checked-out/3775
 */
class PullRequestWithWrongEnviro extends PullRequest
{
    /**
     * The wrong SHA-1 of the merge commit as defined by the environment variables.
     *
     * @var string
     */
    private $wrongMergeSha1;

    /**
     * @param string $baseSha1 the SHA-1 of the base commit (that is, the last commit of the base branch)
     * @param string $headSha1 the SHA-1 of the head commit (that is, the last commit of the pull request branch)
     * @param string $mergeSha1 the SHA-1 of the merge commit
     * @param string $wrongMergeSha1 the wrong SHA-1 of the merge commit as defined by the environment variables
     */
    public function __construct(string $targetBranch, string $baseSha1, string $headSha1, string $mergeSha1, string $wrongMergeSha1)
    {
        parent::__construct($targetBranch, $baseSha1, $headSha1, $mergeSha1);
        $this->wrongMergeSha1 = $wrongMergeSha1;
    }

    /**
     * Get the wrong SHA-1 of the merge commit as defined by the environment variables.
     */
    public function getWrongMergeSha1(): string
    {
        return $this->wrongMergeSha1;
    }
}
