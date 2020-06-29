<?php

declare(strict_types=1);

namespace CIInfo\Driver;

use CIInfo\Driver;
use CIInfo\Env;
use CIInfo\Exception;
use CIInfo\State;

/**
 * The AppVeyor driver.
 */
class AppVeyor implements Driver
{
    use Traits\PullRequestStateTrait;

    /**
     * The AppVeyor driver handle.
     *
     * @var string
     */
    public const HANDLE = 'appveyor';

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
        return 'AppVeyor';
    }

    /**
     * {@inheritdoc}
     *
     * @see \CIInfo\Driver::isCurrent()
     */
    public function isCurrent(Env $env): bool
    {
        return strcasecmp($env->get('APPVEYOR'), 'true') === 0;
    }

    /**
     * {@inheritdoc}
     *
     * @see \CIInfo\Driver::getProjectRootDir()
     */
    public function getProjectRootDir(Env $env): string
    {
        return $env->getNotEmpty('APPVEYOR_BUILD_FOLDER');
    }

    /**
     * {@inheritdoc}
     *
     * @see \CIInfo\Driver::getState()
     */
    public function getState(Env $env): State
    {
        $sha1 = $env->getNotEmpty('APPVEYOR_REPO_COMMIT');
        if ($env->get('APPVEYOR_PULL_REQUEST_HEAD_COMMIT') !== '') {
            return $this->createPullRequestState(
                $env,
                $env->getNotEmpty('APPVEYOR_REPO_BRANCH'),
                $sha1,
                $env->getNotEmpty('APPVEYOR_PULL_REQUEST_HEAD_COMMIT')
            );
        }
        if (strcasecmp($env->get('APPVEYOR_REPO_TAG'), 'true') === 0) {
            return new State\Tag($sha1, $env->getNotEmpty('APPVEYOR_REPO_TAG_NAME'));
        }
        if (strcasecmp($env->get('APPVEYOR_SCHEDULED_BUILD'), 'true') === 0) {
            return new State\Scheduled($env->getNotEmpty('APPVEYOR_REPO_BRANCH'), $sha1);
        }
        if ($env->get('APPVEYOR_FORCED_BUILD', 'true') === 0) {
            return new State\Manual($env->getNotEmpty('APPVEYOR_REPO_BRANCH'), $sha1);
        }

        return new State\PushWithoutBaseCommit(
            $env->getNotEmpty('APPVEYOR_REPO_BRANCH'),
            $sha1,
            new Exception\IncompleteEnvironmentException(
                <<<'EOT'
AppVeyor doesn't provide a way to detect the previous commit of a push event.
For more detauls see:
https://github.com/appveyor/ci/issues/1157 
EOT
            )
        );
    }
}
