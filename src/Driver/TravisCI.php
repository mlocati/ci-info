<?php

declare(strict_types=1);

namespace CIInfo\Driver;

use CIInfo\Driver;
use CIInfo\Env;
use CIInfo\Exception;
use CIInfo\Git;
use CIInfo\State;

/**
 * The Travis CI driver.
 */
class TravisCI implements Driver
{
    use Traits\PullRequestStateTrait {
        createPullRequestState as createPullRequestStateBase;
    }

    /**
     * The Travis CI driver handle.
     *
     * @var string
     */
    public const HANDLE = 'travis-ci';

    /**
     * {@inheritdoc}
     *
     * @see \CIInfo\Driver::getHandle()
     */
    public function getHandle(): string
    {
        return static::HANDLE;
    }

    /**
     * {@inheritdoc}
     *
     * @see \CIInfo\Driver::getName()
     */
    public function getName(): string
    {
        return 'Travis CI';
    }

    /**
     * {@inheritdoc}
     *
     * @see \CIInfo\Driver::isCurrent()
     */
    public function isCurrent(Env $env): bool
    {
        return strcasecmp($env->get('TRAVIS'), 'true') === 0;
    }

    /**
     * {@inheritdoc}
     *
     * @see \CIInfo\Driver::getProjectRootDir()
     */
    public function getProjectRootDir(Env $env): string
    {
        return $env->getNotEmpty('TRAVIS_BUILD_DIR');
    }

    /**
     * {@inheritdoc}
     *
     * @see \CIInfo\Driver::getState()
     */
    public function getState(Env $env): State
    {
        $sha1 = $env->getNotEmpty('TRAVIS_COMMIT');
        $tag = $env->get('TRAVIS_TAG');
        if ($tag !== '') {
            return new State\Tag($sha1, $tag);
        }
        $eventType = $env->getNotEmpty('TRAVIS_EVENT_TYPE');
        switch ($eventType) {
            case 'pull_request':
                return $this->createPullRequestState(
                    $env,
                    $env->getNotEmpty('TRAVIS_BRANCH'),
                    $sha1,
                    $env->getNotEmpty('TRAVIS_PULL_REQUEST_SHA'),
                    $env->get('TRAVIS_COMMIT_RANGE', null) ?: null
                );
            case 'push':
                return $this->createPushState($env);
            case 'cron':
                return new State\Scheduled($env->getNotEmpty('TRAVIS_BRANCH'), $sha1);
            case 'api':
                return new State\Manual($env->getNotEmpty('TRAVIS_BRANCH'), $sha1);
            default:
                throw new Exception\UnexpectedEnvironmentVariableValueException('TRAVIS_EVENT_TYPE', $eventType);
        }
    }

    /**
     * @throws \CIInfo\Exception\MissingEnvironmentVariableException if a required environment variable is missing or empty
     * @throws \CIInfo\Exception\GitFailedException if the git command fails
     * @throws \CIInfo\Exception\GitHistoryTooShortException if the git git history is too short (see git clone --depth)
     * @throws \CIInfo\Exception\InvalidPullRequestStateException if the detection of the pull request state gives bad results
     *
     * @see https://travis-ci.community/t/travis-commit-is-not-the-commit-initially-checked-out/3775
     */
    protected function createPullRequestState(Env $env, string $targetBranch, string $mergeSha1, ?string $expectedHeadSha1 = null, ?string $expectedCommitRange = null): State\PullRequest
    {
        $git = new Git($this->getProjectRootDir($env));
        try {
            $mergeSha1FromGit = $git->getLastCommitSHA1();
            if ($mergeSha1FromGit !== '' && strcasecmp($mergeSha1, $mergeSha1FromGit) !== 0) {
                $correctState = $this->createPullRequestStateFromGitHistory($env, $targetBranch);
                if ($correctState !== null) {
                    return new State\PullRequestWithWrongEnviro(
                        $correctState->getTargetBranch(),
                        $correctState->getBaseSha1(),
                        $correctState->getHeadSha1(),
                        $correctState->getMergeSha1(),
                        $mergeSha1
                    );
                }
            }
        } catch (Exception $x) {
        }

        return $this->createPullRequestStateBase($env, $targetBranch, $mergeSha1, $expectedHeadSha1, $expectedCommitRange);
    }

    /**
     * @throws \CIInfo\Exception\MissingEnvironmentVariableException
     */
    protected function createPushState(Env $env): State\Push
    {
        $branch = $env->getNotEmpty('TRAVIS_BRANCH');
        $actualLastCommitSha1 = $env->getNotEmpty('TRAVIS_COMMIT');
        $rawRange = $env->get('TRAVIS_COMMIT_RANGE');
        if ($rawRange === '') {
            // See https://github.com/travis-ci/docs-travis-ci-com/commit/2b9357a1701c4e33d364a41c8686db05751448ee
            return new State\PushWithoutBaseCommit(
                $branch,
                $actualLastCommitSha1,
                new Exception\PushToNewBranchException()
            );
        }
        $matches = null;
        if (!preg_match('/^([0-9a-fA-F]{6,40})\.\.\.([0-9a-fA-F]{6,40})$/', $rawRange, $matches)) {
            throw new Exception\UnexpectedEnvironmentVariableValueException('TRAVIS_COMMIT_RANGE', $rawRange);
        }
        $baseCommitSha1 = $matches[1];
        $lastCommitSha1 = $matches[2];
        if (stripos($actualLastCommitSha1, $lastCommitSha1) !== 0) {
            throw new Exception\UnexpectedEnvironmentVariableValueException('TRAVIS_COMMIT_RANGE', $rawRange);
        }
        $lastCommitSha1 = $actualLastCommitSha1;
        if (strlen($baseCommitSha1) < 40) {
            $git = new Git($this->getProjectRootDir($env));
            try {
                $baseCommitSha1 = $git->expandShortSha1($baseCommitSha1);
            } catch (Exception $x) {
            }
        }

        return new State\Push($branch, $lastCommitSha1, $baseCommitSha1);
    }
}
