<?php

declare(strict_types=1);

use CIInfo\Env;
use CIInfo\Exception;
use CIInfo\Exception\MissingEnvironmentVariableException;
use CIInfo\State;
use CIInfo\State\PullRequest;
use CIInfo\StateFactory;
use CIInfo\Test\RepositoryTestCase;

class GetPullRequestStateTest extends RepositoryTestCase
{
    /**
     * @dataProvider provideCases
     */
    public function testGetPullRequestState(Env $env, string $expectedExcetion = ''): void
    {
        $env = $env->withPlaceholders([
            static::PLACEHOLDER_PROJECT_DIRECTORY => static::$volatile->getPath(true),
            static::PLACEHOLDER_BASE_SHA1 => static::$baseSHA1,
            static::PLACEHOLDER_HEAD_SHA1 => static::$headSHA1,
            static::PLACEHOLDER_MERGE_SHA1 => static::$mergeSHA1,
        ]);
        $factory = new StateFactory($env);
        $error = null;
        try {
            $state = $factory->getCurrentState();
        } catch (\CIInfo\Exception $x) {
            $error = $x;
        }
        if ($expectedExcetion === '') {
            $this->assertNull($error);
            $this->assertInstanceOf(PullRequest::class, $state);
            $this->assertSame(State::EVENT_PULLREQUEST, $state->getEvent());
            $this->assertSame('master', $state->getTargetBranch());
            $this->assertSame(static::$baseSHA1, $state->getBaseSha1());
            $this->assertSame(static::$headSHA1, $state->getHeadSha1());
            $this->assertSame(static::$mergeSHA1, $state->getMergeSha1());
        } else {
            $this->assertInstanceOf($expectedExcetion, $error);
        }
    }

    public function provideCases(): array
    {
        $appVeyor = $this->getAppVeyorFullEnv();
        $githubActions = $this->getGithubActionsFullEnv();
        $travisCI = $this->getTravisCIFullEnv();

        return [
            [new Env([]), Exception\NoApplicableDriverException::class],
            [$appVeyor],
            [$appVeyor->without(['APPVEYOR_BUILD_FOLDER']), MissingEnvironmentVariableException::class],
            [$githubActions],
            [$travisCI],
        ];
    }

    private function getAppVeyorFullEnv(): Env
    {
        return new Env([
            'CI' => 'True',
            'CI_LINUX' => 'False',
            'CI_WINDOWS' => 'True',
            'APPVEYOR' => 'True',
            'APPVEYOR_ACCOUNT_NAME' => 'johndoe',
            'APPVEYOR_API_URL' => 'http://localhost:1033/',
            'APPVEYOR_BUILD_AGENT_HYPERV_NIC_CONFIGURED: true',
            'APPVEYOR_BUILD_FOLDER' => static::PLACEHOLDER_PROJECT_DIRECTORY,
            'APPVEYOR_BUILD_ID' => '33711628',
            'APPVEYOR_BUILD_NUMBER' => '86',
            'APPVEYOR_BUILD_VERSION' => '1.0.86',
            'APPVEYOR_BUILD_WORKER_IMAGE' => 'Visual Studio 2015',
            'APPVEYOR_JOB_ID' => 'b3r6s2cxo6ltwq87',
            'APPVEYOR_JOB_NUMBER' => '1',
            'APPVEYOR_PROJECT_ID' => '690577',
            'APPVEYOR_PROJECT_NAME' => 'foobar',
            'APPVEYOR_PROJECT_SLUG' => 'foobar',
            'APPVEYOR_PULL_REQUEST_HEAD_COMMIT' => static::PLACEHOLDER_HEAD_SHA1,
            'APPVEYOR_PULL_REQUEST_HEAD_REPO_BRANCH' => static::PULLREQUEST_BRANCH_NAME,
            'APPVEYOR_PULL_REQUEST_HEAD_REPO_NAME' => 'johndoe/foobar',
            'APPVEYOR_PULL_REQUEST_NUMBER' => '3',
            'APPVEYOR_PULL_REQUEST_TITLE' => 'PR2',
            'APPVEYOR_REPO_BRANCH' => 'master',
            'APPVEYOR_REPO_COMMIT' => static::PLACEHOLDER_MERGE_SHA1,
            'APPVEYOR_REPO_COMMIT_AUTHOR' => static::AUTHOR_NAME,
            'APPVEYOR_REPO_COMMIT_AUTHOR_EMAIL' => static::AUTHOR_EMAIL,
            'APPVEYOR_REPO_COMMIT_MESSAGE' => self::LAST_HEAD_COMMIT_MESSAGE,
            'APPVEYOR_REPO_COMMIT_TIMESTAMP' => '2020-06-24T12:58:36.0000000Z',
            'APPVEYOR_REPO_NAME' => 'johndoe/foobar',
            'APPVEYOR_REPO_PROVIDER' => 'gitHub',
            'APPVEYOR_REPO_SCM' => 'git',
            'APPVEYOR_REPO_TAG' => 'false',
            'APPVEYOR_URL' => 'https://ci.appveyor.com',
        ]);
    }

    private function getGithubActionsFullEnv(): Env
    {
        return new Env([
            'CI' => 'true',
            'GITHUB_ACTION' => 'run2',
            'GITHUB_ACTIONS' => 'true',
            'GITHUB_ACTOR' => 'johndoe',
            'GITHUB_API_URL' => 'https://api.github.com',
            'GITHUB_BASE_REF' => 'master',
            'GITHUB_EVENT_NAME' => 'pull_request',
            'GITHUB_EVENT_PATH' => '/home/runner/work/_temp/_github_workflow/event.json',
            'GITHUB_GRAPHQL_URL' => 'https://api.github.com/graphql',
            'GITHUB_HEAD_REF' => static::PULLREQUEST_BRANCH_NAME,
            'GITHUB_JOB' => 'test',
            'GITHUB_REF' => 'refs/pull/3/merge',
            'GITHUB_REPOSITORY' => 'johndoe/foobar',
            'GITHUB_REPOSITORY_OWNER' => 'johndoe',
            'GITHUB_RUN_ID' => '146182483',
            'GITHUB_RUN_NUMBER' => '87',
            'GITHUB_SERVER_URL' => 'https://github.com',
            'GITHUB_SHA' => static::PLACEHOLDER_MERGE_SHA1,
            'GITHUB_WORKFLOW' => 'Test',
            'GITHUB_WORKSPACE' => static::PLACEHOLDER_PROJECT_DIRECTORY,
        ]);
    }

    private function getTravisCIFullEnv(): Env
    {
        $timestamp = time();

        return new Env([
            'CI' => 'true',
            'TRAVIS' => 'true',
            'TRAVIS_ALLOW_FAILURE' => '',
            'TRAVIS_APP_HOST' => 'build.travis-ci.org',
            'TRAVIS_APT_PROXY' => 'http://build-cache.travisci.net',
            'TRAVIS_ARCH' => 'amd64',
            'TRAVIS_BRANCH' => 'master',
            'TRAVIS_BUILD_DIR' => static::PLACEHOLDER_PROJECT_DIRECTORY,
            'TRAVIS_BUILD_ID' => '701637617',
            'TRAVIS_BUILD_NUMBER' => '85',
            'TRAVIS_BUILD_STAGE_NAME' => '',
            'TRAVIS_BUILD_WEB_URL' => 'https://travis-ci.org/johndoe/foobar/builds/701637617',
            'TRAVIS_CMD' => 'php test.php',
            'TRAVIS_COMMIT' => static::PLACEHOLDER_MERGE_SHA1,
            'TRAVIS_COMMIT_MESSAGE' => 'Merge ' . static::PLACEHOLDER_HEAD_SHA1 . ' into ' . static::PLACEHOLDER_BASE_SHA1,
            'TRAVIS_COMMIT_RANGE' => static::PLACEHOLDER_BASE_SHA1 . '...' . static::PLACEHOLDER_HEAD_SHA1,
            'TRAVIS_CPU_ARCH' => 'amd64',
            'TRAVIS_DIST' => 'xenial',
            'TRAVIS_ENABLE_INFRA_DETECTION' => 'true',
            'TRAVIS_EVENT_TYPE' => 'pull_request',
            'TRAVIS_HOME' => '/home/travis',
            'TRAVIS_INFRA' => 'gce',
            'TRAVIS_INIT' => 'systemd',
            'TRAVIS_INTERNAL_RUBY_REGEX' => '^ruby-(2\.[0-4]\.[0-9]|1\.9\.3)',
            'TRAVIS_JOB_ID' => '701637618',
            'TRAVIS_JOB_NAME' => '',
            'TRAVIS_JOB_NUMBER' => '85.1',
            'TRAVIS_JOB_WEB_URL' => 'https://travis-ci.org/johndoe/foobar/jobs/701637618',
            'TRAVIS_LANGUAGE' => 'php',
            'TRAVIS_OSX_IMAGE' => '',
            'TRAVIS_OS_NAME' => 'linux',
            'TRAVIS_PHP_VERSION' => '7.2',
            'TRAVIS_PRE_CHEF_BOOTSTRAP_TIME' => '2019-03-25T16:16:42',
            'TRAVIS_PULL_REQUEST' => '3',
            'TRAVIS_PULL_REQUEST_BRANCH' => static::PULLREQUEST_BRANCH_NAME,
            'TRAVIS_PULL_REQUEST_SHA' => static::PLACEHOLDER_HEAD_SHA1,
            'TRAVIS_PULL_REQUEST_SLUG' => 'johndoe/foobar',
            'TRAVIS_REPO_SLUG' => 'johndoe/foobar',
            'TRAVIS_ROOT' => '/',
            'TRAVIS_SECURE_ENV_VARS' => 'false',
            'TRAVIS_STACK_FEATURES' => 'basic couchdb disabled-ipv6 docker docker-compose elasticsearch firefox go-toolchain google-chrome jdk memcached mongodb mysql nodejs_interpreter perl_interpreter perlbrew phantomjs postgresql python_interpreter redis ruby_interpreter sqlite xserver',
            'TRAVIS_STACK_JOB_BOARD_REGISTER' => '/.job-board-register.yml',
            'TRAVIS_STACK_LANGUAGES' => '__sardonyx__ c c++ clojure cplusplus cpp default generic go groovy java node_js php pure_java python ruby scala',
            'TRAVIS_STACK_NAME' => 'sardonyx',
            'TRAVIS_STACK_NODE_ATTRIBUTES' => '/.node-attributes.yml',
            'TRAVIS_STACK_TIMESTAMP' => date('Y-m-d H:i:s e', $timestamp),
            'TRAVIS_SUDO' => 'true',
            'TRAVIS_TAG' => '',
            'TRAVIS_TEST_RESULT' => '',
            'TRAVIS_TIMER_ID' => '21b54c28',
            'TRAVIS_TIMER_START_TIME' => "{$timestamp}123456789",
            'TRAVIS_TMPDIR' => '/tmp/tmp.3XZubuPQGf',
            'TRAVIS_UID' => '2000',
        ]);
    }
}
