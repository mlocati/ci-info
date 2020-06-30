<?php

declare(strict_types=1);

namespace CIInfo;

/**
 * Base class for all the CI states.
 */
interface State
{
    /**
     * Event identifier: push.
     *
     * @var string
     */
    public const EVENT_PUSH = 'push';

    /**
     * Event identifier: pull request.
     *
     * @var string
     */
    public const EVENT_PULLREQUEST = 'pr';

    /**
     * Event identifier: tag created.
     *
     * @var string
     */
    public const EVENT_TAG = 'tag';

    /**
     * Event identifier: scheduled execution.
     *
     * @var string
     */
    public const EVENT_SCHEDULED = 'scheduled';

    /**
     * Event identifier: manual trigger (via APIs, web requests, ...).
     *
     * @var string
     */
    public const EVENT_MANUAL = 'manual';

    /**
     * Get the event type (it's the value of one of the State::EVENT__... constants).
     *
     * @return string
     */
    public function getEvent(): string;

    /**
     * Get the SHA-1 of the last commit.
     */
    public function getLastCommitSha1(): string;
}
