<?php

declare(strict_types=1);

namespace CIInfo\State;

use CIInfo\Exception\IncompleteEnvironmentException;

/**
 * The state representing a push build when the base commit is not available.
 */
class PushWithoutBaseCommit extends Push
{
    /**
     * @var \CIInfo\Exception\IncompleteEnvironmentException
     */
    private $baseCommitUnavilableDescription;

    /**
     * @param string $branch the name of the branch affected by the push
     * @param string $lastCommitSha1 the SHA-1 of the head commit (that is, the last commit of the push)
     * @param \CIInfo\Exception\IncompleteEnvironmentException a description about the problem detecting the base commit SHA-1
     */
    public function __construct(string $branch, string $lastCommitSha1, IncompleteEnvironmentException $baseCommitUnavilableDescription)
    {
        parent::__construct($branch, $lastCommitSha1, '');
        $this->baseCommitUnavilableDescription = $baseCommitUnavilableDescription;
    }

    /**
     * {@inheritdoc}
     *
     * @see \CIInfo\State\Push::getBaseCommitSha1()
     */
    public function getBaseCommitSha1(): string
    {
        throw $this->baseCommitUnavilableDescription;
    }

    public function describeWhiBaseCommitIsUnavailable(): string
    {
        return $this->baseCommitUnavilableDescription->getMessage();
    }
}
