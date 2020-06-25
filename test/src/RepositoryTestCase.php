<?php

declare(strict_types=1);

namespace CIInfo\Test;

use CIInfo\Git;
use PHPUnit\Framework\TestCase;
use Throwable;

abstract class RepositoryTestCase extends TestCase
{
    /**
     * @var string
     */
    protected const PULLREQUEST_BRANCH_NAME = 'my-pr-branch';

    /**
     * @var string
     */
    protected const AUTHOR_NAME = 'John Doe';

    /**
     * @var string
     */
    protected const AUTHOR_EMAIL = 'foo@bar.baz';

    /**
     * @var string
     */
    protected const LAST_HEAD_COMMIT_MESSAGE = 'Last in branch';

    /**
     * @var string
     */
    protected const PLACEHOLDER_PROJECT_DIRECTORY = '<project-directory>';

    /**
     * @var string
     */
    protected const PLACEHOLDER_BASE_SHA1 = '<base-sha1>';

    /**
     * @var string
     */
    protected const PLACEHOLDER_HEAD_PREV_SHA1 = '<head-prev-sha1>';

    /**
     * @var string
     */
    protected const PLACEHOLDER_HEAD_SHA1 = '<head-sha1>';

    /**
     * @var string
     */
    protected const PLACEHOLDER_MERGE_SHA1 = '<merge-sha1>';

    /**
     * @var \CIInfo\Test\VolatileDirectory
     */
    protected static $volatile;

    /**
     * @var string
     */
    protected static $baseSHA1;

    /**
     * @var string
     */
    protected static $headPrevSHA1;

    /**
     * @var string
     */
    protected static $headSHA1;

    /**
     * @var string
     */
    protected static $mergeSHA1;

    /**
     * {@inheritdoc}
     *
     * @see \PHPUnit\Framework\TestCase::setUpBeforeClass()
     */
    public static function setUpBeforeClass(): void
    {
        self::$volatile = new VolatileDirectory();
        try {
            $git = new Git(self::$volatile->getPath());
            // Initialize repository
            $git->run('init');
            $git->run('config user.email ' . escapeshellarg(static::AUTHOR_EMAIL));
            $git->run('config user.name ' . escapeshellarg(static::AUTHOR_NAME));
            $git->run('config commit.gpgsign false');
            // 3 commits in master
            $git->run('commit --allow-empty -m "Initial"');
            $git->run('commit --allow-empty -m "Last Common"');
            $lastCommonSHA1 = $git->run('log --max-count=1 --format=format:%H HEAD')[0];
            $git->run('commit --allow-empty -m "Last in master"');
            self::$baseSHA1 = $baseSHA1 = $git->run('log --max-count=1 --format=format:%H HEAD')[0];
            // create branch, starting from 1 commit before the last in master
            $git->run('checkout --no-track -b ' . static::PULLREQUEST_BRANCH_NAME . " {$lastCommonSHA1}");
            $git->run('commit --allow-empty -m "First commit in branch"');
            self::$headPrevSHA1 = $headSHA1 = $git->run('log --max-count=1 --format=format:%H HEAD')[0];
            $git->run('commit --allow-empty -m ' . escapeshellarg(static::LAST_HEAD_COMMIT_MESSAGE));
            self::$headSHA1 = $headSHA1 = $git->run('log --max-count=1 --format=format:%H HEAD')[0];
            $git->run('checkout master');
            $git->run("merge --no-ff -m \"Merge {$headSHA1} into {$baseSHA1}\" {$headSHA1}");
            self::$mergeSHA1 = $git->run('log --max-count=1 --format=format:%H HEAD')[0];
        } catch (Throwable $x) {
            self::$volatile = null;
            throw $x;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see \PHPUnit\Framework\TestCase::setUpBeforeClass()
     */
    public static function tearDownAfterClass(): void
    {
        self::$volatile = null;
    }
}
