<?php

declare(strict_types=1);

namespace CIInfo;

use CIInfo\Exception\GitFailedException;
use CIInfo\Exception\GitHistoryTooShortException;

/**
 * Helper class to make it easier running git commands.
 */
class Git
{
    /**
     * The root directory of the git repository.
     *
     * @var string
     */
    private $rootDirectory;

    /**
     * Initialize the instance.
     *
     * @param string $rootDirectory the root directory of the git repository
     */
    public function __construct(string $rootDirectory)
    {
        $this->rootDirectory = $rootDirectory;
    }

    /**
     * @throws \CIInfo\Exception\GitFailedException if the command fails
     *
     * @return string[]
     */
    public function run(string $args): array
    {
        $error = null;
        set_error_handler(
            function ($errno, $errstr) use (&$error): void {
                $error = trim((string) $errstr);
                if ($error === '') {
                    $error = "Unknown error (code: {$errno})";
                }
            },
            -1
        );
        $rc = -1;
        $output = [];
        try {
            exec('git -C ' . escapeshellarg(str_replace('/', DIRECTORY_SEPARATOR, $this->rootDirectory)) . ' ' . $args . ' 2>&1', $output, $rc);
        } finally {
            restore_error_handler();
        }
        if ($rc !== 0) {
            $output = trim(implode("\n", $output));
            if ($error === null) {
                $error = $output;
            } elseif ($output !== '') {
                $error .= "\n{$output}";
            }
            if ($error === '') {
                $error = 'Unknown problem';
            }
            throw new GitFailedException($args, $error);
        }

        return $output;
    }

    /**
     * Inspect the git tree to detect the SHA-1 of the commits associated to the merge commit.
     *
     * @throws \CIInfo\Exception\GitFailedException if the git command fails
     * @throws \CIInfo\Exception\GitHistoryTooShortException if the git git history is too short (see git clone --depth)
     *
     * @return array first value is the SHA-1 of the merge commit, second value is the SHA-1 of the base branch, third value is the SHA-1 of the head branch
     */
    public function getMergeParentsFromHistory(string $ref = 'HEAD'): array
    {
        $command = 'rev-list --parents -n1 ' . escapeshellarg($ref);
        $rawParents = $this->run($command);
        $parents = preg_split('/\s+/', $rawParents[0] ?? '', -1, PREG_SPLIT_NO_EMPTY);
        switch (count($parents)) {
            case 1:
                throw new GitHistoryTooShortException();
            case 3:
                return $parents;
            default:
                throw new GitFailedException($command, "Failed to extract pull request parents from:\n" . trim(implode("\n", $rawParents)));
        }
    }

    /**
     * Get the last commit SHA-1.
     *
     * @throws \CIInfo\Exception\GitFailedException if the git command fails
     */
    public function getLastCommitSHA1(string $ref = 'HEAD'): string
    {
        $lines = $this->run('log --max-count=1 --format=format:%H ' . escapeshellarg($ref));

        return trim(implode("\n", $lines));
    }

    /**
     * Get the last commit message.
     *
     * @throws \CIInfo\Exception\GitFailedException if the git command fails
     */
    public function getLastCommitMessage(string $ref = 'HEAD', bool $onlyFirstLine = true): string
    {
        $lines = $this->run('log --max-count=1 --format=format:%' . ($onlyFirstLine ? 's' : 'B') . '  ' . escapeshellarg($ref));

        return trim(implode("\n", $lines));
    }

    /**
     * Expand a the short version of commit SHA-1 to its full version.
     */
    public function expandShortSha1(string $shortSha1): string
    {
        $command = 'rev-parse ' . escapeshellarg($shortSha1);
        $output = $this->run($command);
        if (count($output) === 1 && strlen($output[0]) === 40) {
            return $output[0];
        }
        throw new GitFailedException($command, "Failed to retrieve the full SHA-1 of '{$shortSha1}':\n" . trim(implode("\n", $output)));
    }
}
