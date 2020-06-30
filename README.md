# CI-Info

This package lets you get details about the current Continuous Integration environment.

Supported environments are:

- AppVeyor
- GitHub Actions
- TravisCI

## Installation

You can install this package in two ways:

- using [composer](https://getcomposer.org):
  ```
  composer require mlocati/ci-info
  ```
- manually: download a ZIP archive from the [project Releases page](https://github.com/mlocati/ci-info/releases).

## Usage from within your PHP scripts

### Setup

First of all, __if you don't use Composer__, you need to include the `autoload.php` file you can find in the root directory of this project:

```php
require_once 'path/to/autoload.php';
```

## Usage

### Determine the current CI Environment

```php
$driver = (new \CIInfo\DriverList())->getDriverForEnvironment();
if ($driver === null) {
    // CI environment Not detected
} else {
    if ($ci->getHandle() === \CIInfo\Driver\GithubActions::HANDLE) {
        // We are running in a GitHub Actions build
    }
}
```

### Get info about the current job

```php
try {
    $state = (new \CIInfo\StateFactory())->getCurrentState();
} catch (\CIInfo\Exception $whoops) {
    echo "Something went wrong: " . (string) $whoops;
    return;
}

switch ($state->getEvent()) {
    case \CIInfo\State::EVENT_PUSH:
        // $state is an instance of the \CIInfo\State\Push (or its \CIInfo\State\PushWithoutBaseCommit subclass) class
        echo "We are in a build triggered by a push.\n";
        break;
    case \CIInfo\State::EVENT_PULLREQUEST:
        // $state is an instance of the \CIInfo\State\PullRequest class  
        echo "We are in a build triggered by a pull request.\n";
        break;
    case \CIInfo\State::EVENT_TAG:
        // $state is an instance of the \CIInfo\State\Tag class
        echo "We are in a build triggered by the creation of a tag.\n";
        break;
    case \CIInfo\State::EVENT_SCHEDULED:
        // $state is an instance of the \CIInfo\State\Scheduled class
        echo "We are in a build triggered by a scheduled job.\n";
        break;
    case \CIInfo\State::EVENT_MANUAL:
        // $state is an instance of the \CIInfo\State\Manual class
        echo "We are in a build triggered manually (via APIs, manual builds, repository_dispatch events, ...).\n";
        break;
}
```

To see the methods available for every class, see the [source code](https://github.com/mlocati/ci-info/tree/master/src/State).

## Usage from a shell

You can also use this library in your shell scripts (bash, sh, powershell, ...).

First of all, you have to determine the path of the `ci-info` file. It's under the `bin` directory (or `composer/vendor/bin` for composer-based projects).

`ci-info` can provide details about the current environment/job.

Here's a sample POSIX script:

```sh
$ driver="$(./bin/ci-info driver)"
$ echo "The current CI environment is: $driver"
The current CI environment is: travis-ci
```

Here's a sample PowerShell script:

```sh
PS> $driver="$(.\bin\ci-info driver)"
PS> Write-Host "The current CI environment is: $driver"
The current CI environment is: github-actions
```

To get the full list of the features of the `ci-info` command, type:

```sh
$ ./bin/ci-info --help
```

Which outputs:

<!-- CI-INFO-HELP-START -->
```
Syntax:
  ./bin/ci-info [-q|--quiet] [-h|--help] <command>

Options:
-q|--quiet: turn off displaying errors
-h|--help : show this syntax message and quits

Allowed values for <command> are:
# driver
Print the handle identifying the current environment.
Possible results are:
- appveyor: AppVeyor
- github-actions: GitHub Actions
- travis-ci: Travis CI

# event
Print the current operation type.
Possible results are:
- cron: Scheduled event
- manual: Manually triggered event (API calls, repository_dispatch events, forced rebuilds, ...)
- pr: Pull request event
- push: Push event
- tag: Tag creation event

# sha1
Print the SHA-1 of the most recent commit (it's the merge commit in case of pull requests)

# pr:base:branch
Print the target branch of a pull request

# pr:base:sha1
Print the SHA-1 of the last commit in the target branch

# pr:head:sha1
Print the SHA-1 of the last commit in the pull request branch

# pr:wrongsha1
Print the wrong SHA-1 of the merge commit of a pull request event as defined by the current environment.
For example, the TRAVIS_COMMIT environment variable defined in TravisCI may be wrong (see https://travis-ci.community/t/travis-commit-is-not-the-commit-initially-checked-out/3775 )
If there merge commit SHA-1 is correct, nothing gets printed out. 

# pr:range
Print the commit range of pull request events (example: 123456abcded...abcded123456)

# push:branch
Print the name of the branch affected by a push event

# push:prev:sha1
Print the SHA-1 of the commit prior to the last commit for a push event

# push:range
Print the commit range of push events (example: 123456abcded...abcded123456)

# tag:name
Print the tag name (for tag jobs)

# manual:branch
Print the current branch in a manually-triggered job

# cron:branch
Print the current branch in a scheduled job

Exit code:
0: success
1: failure
```
<!-- CI-INFO-HELP-END -->

## Tests

This library is tested against two types of cases:

- Offline tests, implemented as GitHub Actions and managed by phpunit, which test the library against well-known environment statuses of the supported CI environment
  - Status: [![Offline Tests](https://github.com/mlocati/ci-info/workflows/Offline%20Tests/badge.svg)](https://github.com/mlocati/ci-info/actions?query=workflow%3A%22Offline+Tests%22)
- Online tests, executed directly in every supported CI enviromnent
  - AppVeyor: [![AppVeyor Online Tests](https://ci.appveyor.com/api/projects/status/g1d445s45p8lrs2t?svg=true)](https://ci.appveyor.com/project/mlocati/ci-info/history)
  - GitHub Actions: [![GitHub Actions Online Tests](https://github.com/mlocati/ci-info/workflows/Online%20Tests/badge.svg)](https://github.com/mlocati/ci-info/actions?query=workflow%3A%22Online+Tests%22)
  - TravisCI:
    - Push tests: [![TravisCI Online Tests for pushes](https://travis-ci.org/mlocati/ci-info.svg?branch=master)](https://travis-ci.org/github/mlocati/ci-info/branches)  
    - Pull request tests: [![TravisCI Online Tests for pull requests](https://travis-ci.org/mlocati/ci-info.svg?branch=master)](https://travis-ci.org/github/mlocati/ci-info/pull_requests)  
