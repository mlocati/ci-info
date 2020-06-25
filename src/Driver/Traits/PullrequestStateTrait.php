<?php

declare(strict_types=1);

namespace CIInfo\Driver\Traits;

use CIInfo\Env;
use CIInfo\Exception\GitHistoryTooShortException;
use CIInfo\Exception\InvalidPullRequestStateException;
use CIInfo\Git;
use CIInfo\State\PullRequest;

trait PullrequestStateTrait
{
    /**
     * @throws \CIInfo\Exception\MissingEnvironmentVariableException if a required environment variable is missing or empty
     * @throws \CIInfo\Exception\GitFailedException if the git command fails
     * @throws \CIInfo\Exception\GitHistoryTooShortException if the git git history is too short (see git clone --depth)
     * @throws \CIInfo\Exception\InvalidPullRequestStateException if the detection of the pull request state gives bad results
     */
    protected function createPullRequestState(Env $env, string $targetBranch, string $mergeSha1, ?string $expectedHeadSha1 = null, ?string $expectedCommitRange = null): PullRequest
    {
        $result = $this->createPullRequestStateFromLastCommit($env, $targetBranch, $mergeSha1);
        try {
            $result = $this->checkSamePullRequestStates($result, $this->createPullRequestStateFromGitHistory($env, $targetBranch));
        } catch (GitHistoryTooShortException $x) {
            if ($result === null) {
                throw $x;
            }
        }
        if ($result->getMergeSha1() !== $mergeSha1) {
            throw new InvalidPullRequestStateException(
                <<<EOT
Detected merge SHA-1: {$result->getMergeSha1()}
Expected merge SHA-1: {$mergeSha1}
EOT
            );
        }
        if ($expectedHeadSha1 !== null && $result->getHeadSha1() !== $expectedHeadSha1) {
            throw new InvalidPullRequestStateException(
                <<<EOT
Detected head SHA-1: {$result->getHeadSha1()}
Expected head SHA-1: {$expectedHeadSha1}
EOT
            );
        }
        if ($expectedCommitRange !== null && $result->getCommitRange() !== $expectedCommitRange) {
            throw new InvalidPullRequestStateException(
                <<<EOT
Detected commit range: {$result->getCommitRange()}
Expected commit range: {$expectedCommitRange}
EOT
            );
        }

        return $result;
    }

    /**
     * Create a PullRequest instance starting from the last commit message.
     *
     * @throws \CIInfo\Exception\GitFailedException if the git command fails
     *
     * @return \CIInfo\State\PullRequest|null return NULL if the last commit message is not in the expected format
     */
    protected function createPullRequestStateFromLastCommit(Env $env, string $targetBranch, string $mergeSha1): ?PullRequest
    {
        $git = new Git($this->getProjectRootDir($env));
        $subject = $git->getLastCommitMessage();
        $matches = null;
        if (!preg_match('/^Merge ([0-9a-fA-F]{40}) into ([0-9a-fA-F]{40})$/', $subject, $matches)) {
            return null;
        }
        $headSha1 = $matches[1];
        $baseSha1 = $matches[2];

        return new PullRequest($targetBranch, $baseSha1, $headSha1, $mergeSha1);
    }

    /**
     * Create a PullRequest instance by inspecting the git log.
     *
     * @throws \CIInfo\Exception\GitFailedException if the git command fails
     * @throws \CIInfo\Exception\GitHistoryTooShortException if the git git history is too short (see git clone --depth)
     *
     * @return \CIInfo\State\PullRequest
     */
    protected function createPullRequestStateFromGitHistory(Env $env, string $targetBranch): ?PullRequest
    {
        $git = new Git($this->getProjectRootDir($env));
        list($mergeSha1, $baseSha1, $headSha1) = $git->getMergeParentsFromHistory();

        return new PullRequest($targetBranch, $baseSha1, $headSha1, $mergeSha1);
    }

    /**
     * Check that two PullRequest instances are the same.
     *
     * @throws \CIInfo\Exception\InvalidPullRequestStateException if both states are not null and contain different values
     *
     * @return \CIInfo\State\PullRequest|null The first non-null state
     */
    protected function checkSamePullRequestStates(?PullRequest $state1, ?PullRequest $state2): ?PullRequest
    {
        if ($state1 === null) {
            return $state2;
        }
        if ($state2 === null) {
            return $state1;
        }
        if ($state1->getBaseSha1() !== $state2->getBaseSha1()) {
            throw new InvalidPullRequestStateException(
                <<<EOT
Base SHA-1 with method #1: {$state1->getBaseSha1()}
Base SHA-1 with method #2: {$state2->getBaseSha1()}
EOT
            );
        }
        if ($state1->getHeadSha1() !== $state2->getHeadSha1()) {
            throw new InvalidPullRequestStateException(
                <<<EOT
Head SHA-1 with method #1: {$state1->getHeadSha1()}
Head SHA-1 with method #2: {$state2->getHeadSha1()}
EOT
            );
        }
        if ($state1->getMergeSha1() !== $state2->getMergeSha1()) {
            throw new InvalidPullRequestStateException(
                <<<EOT
Merge SHA-1 with method #1: {$state1->getHeadSha1()}
Merge SHA-1 with method #2: {$state2->getHeadSha1()}
EOT
            );
        }

        return $state1;
    }
}
