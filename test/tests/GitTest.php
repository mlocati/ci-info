<?php

declare(strict_types=1);

use CIInfo\Exception\GitHistoryTooShortException;
use CIInfo\Git;
use CIInfo\Test\RepositoryTestCase;
use CIInfo\Test\VolatileDirectory;

class GitTest extends RepositoryTestCase
{
    public function testGitLastCommit(): void
    {
        $git = new Git(self::$volatile->getPath());
        $this->assertRegExp('/^Merge [0-9a-fA-F]{40} into [0-9a-fA-F]{40}$/', $git->getLastCommitMessage());
    }

    public function testGitAnalyzeHistory(): void
    {
        $git = new Git(self::$volatile->getPath());
        list($mergeSHA1, $baseSHA1, $headSHA1) = $git->getMergeParentsFromHistory();
        $this->assertSame($baseSHA1, self::$baseSHA1);
        $this->assertSame($headSHA1, self::$headSHA1);
        $this->assertSame($mergeSHA1, self::$mergeSHA1);
    }

    public function testInsufficientDepth(): void
    {
        $volatile = new VolatileDirectory();
        $git = new Git($volatile->getPath());
        $git->run(implode(' ', [
            'clone --no-hardlinks --dissociate --depth 1',
            escapeshellarg('file://' . self::$volatile->getPath(true)),
            escapeshellarg($volatile->getPath(true)),
        ]));
        $this->expectException(GitHistoryTooShortException::class);
        $git->getMergeParentsFromHistory();
    }
}
