<?php

declare(strict_types=1);

namespace CIInfo\Driver;

use CIInfo\Driver;
use CIInfo\Env;
use CIInfo\Exception;
use CIInfo\State;

/**
 * The GitHub Actions driver.
 */
class GithubActions implements Driver
{
    use Traits\PullrequestStateTrait;

    /**
     * The GitHub Actions driver handle.
     *
     * @var string
     */
    public const HANDLE = 'github-actions';

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
     * @see \CIInfo\Driver::getProjectRootDir()
     */
    public function getProjectRootDir(Env $env): string
    {
        return $env->getNotEmpty('GITHUB_WORKSPACE');
    }

    /**
     * {@inheritdoc}
     *
     * @see \CIInfo\Driver::isCurrent()
     */
    public function isCurrent(Env $env): bool
    {
        return strcasecmp($env->get('GITHUB_ACTIONS'), 'true') === 0;
    }

    /**
     * {@inheritdoc}
     *
     * @see \CIInfo\Driver::getState()
     */
    public function getState(Env $env): State
    {
        $sha1 = $env->getNotEmpty('GITHUB_SHA');
        $eventName = $env->getNotEmpty('GITHUB_EVENT_NAME');
        if ($eventName === 'pull_request') {
            return $this->createPullRequestState(
                $env,
                $env->getNotEmpty('GITHUB_BASE_REF'),
                $sha1
            );
        }
        $matches = null;
        if ($eventName === 'create' && preg_match('%^refs/tags/(.+)%', $env->get('GITHUB_REF'), $matches)) {
            return new State\Tag($sha1, $matches[1]);
        }
        switch ($eventName) {
            case 'push':
                return $this->createPushState($env);
            case 'schedule':
                return new State\Scheduled($this->extractBranchFromRef($env), $sha1);
            case 'repository_dispatch':
                return new State\Manual($this->extractBranchFromRef($env), $sha1);
            default:
                throw new Exception\UnexpectedEnvironmentVariableValueException('GITHUB_EVENT_NAME', $eventName);
        }
    }

    /**
     * @throws \CIInfo\Exception\MissingEnvironmentVariableException
     */
    protected function createPushState(Env $env): State\Push
    {
        $ref = $env->getNotEmpty('GITHUB_REF');
        $matches = null;
        if (!preg_match('%^refs/heads/(.+)$%', $ref, $matches)) {
            throw new Exception\UnexpectedEnvironmentVariableValueException('GITHUB_REF', $ref);
        }
        $branch = $this->extractBranchFromRef($env);
        $lastCommitSha1 = $env->getNotEmpty('GITHUB_SHA');
        $baseCommitSha1 = '';
        $context = $env->get('GITHUB_CONTEXT');
        if ($context !== '') {
            set_error_handler(function () {}, -1);
            $context = json_decode($context, true);
            restore_error_handler();
            if (is_array($context)) {
                $baseCommitSha1 = $context['event']['before'] ?? '';
            }
        }

        if ($baseCommitSha1 !== '') {
            return new State\Push($branch, $lastCommitSha1, $baseCommitSha1);
        }

        return new State\PushWithoutBaseCommit(
            $branch,
            $lastCommitSha1,
            new Exception\IncompleteEnvironmentException(
                <<<'EOT'
There's no built-in environment variables in GitHub Actions holding the previous commit of a push event.
A workaround is to pass the github state to the build job by setting a GITHUB_CONTEXT environment variable.
Example:

jobs:
  your_job:
    steps:
      - name: Your Step
        env:
          GITHUB_CONTEXT: ${{ toJson(github) }}
        run: your-script
EOT
            )
        );
    }

    /**
     * @throws \CIInfo\Exception\MissingEnvironmentVariableException
     * @throws \CIInfo\Exception\UnexpectedEnvironmentVariableValueException
     */
    protected function extractBranchFromRef(Env $env): string
    {
        $ref = $env->getNotEmpty('GITHUB_REF');
        $matches = null;
        if (!preg_match('%^refs/heads/(.+)$%', $ref, $matches)) {
            throw new Exception\UnexpectedEnvironmentVariableValueException('GITHUB_REF', $ref);
        }

        return $matches[1];
    }
}
