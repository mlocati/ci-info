<?php

declare(strict_types=1);

namespace CIInfo;

/**
 * Interface that every CI driver must implement.
 */
interface Driver
{
    /**
     * Get the unique handle of the driver.
     */
    public function getHandle(): string;

    /**
     * Is this driver applicable for the passed environment variables?
     */
    public function isCurrent(Env $env): bool;

    /**
     * Get the project root directory.
     *
     * @throws \CIInfo\Exception\MissingEnvironmentVariableException if a required environment variable is missing or empty
     */
    public function getProjectRootDir(Env $env): string;

    /**
     * Get the current CI state.
     *
     * @param \CIInfo\Env $env
     *
     * @throws \CIInfo\Exception\MissingEnvironmentVariableException if a required environment variable is missing or empty
     * @throws \CIInfo\Exception\UnexpectedEnvironmentVariableValueException if an environment variable contains an unexpected value
     * @throws \CIInfo\Exception\GitFailedException if the git command fails
     * @throws \CIInfo\Exception\GitHistoryTooShortException if the git git history is too short (see git clone --depth)
     * @throws \CIInfo\Exception\InvalidPullRequestStateException if the detection of the pull request state gives bad results
     *
     * @return \CIInfo\State
     */
    public function getState(Env $env): State;
}
