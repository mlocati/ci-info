<?php

declare(strict_types=1);

use CIInfo\Env;
use CIInfo\Exception;
use CIInfo\Git;
use CIInfo\State;
use CIInfo\StateFactory;
use CIInfo\Test\RepositoryTestCase;

class GetPushStateTest extends RepositoryTestCase
{
    /**
     * @var string
     */
    protected const PLACEHOLDER_HEAD_PREV_SHA1_12 = '<head-prev-sha1:12>';

    /**
     * @var string
     */
    protected const PLACEHOLDER_HEAD_SHA1_12 = '<head-sha1:12>';

    /**
     * {@inheritdoc}
     *
     * @see \CIInfo\Test\RepositoryTestCase::setUpBeforeClass()
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        try {
            $git = new Git(static::$volatile->getPath());
            $git->run('checkout ' . static::PULLREQUEST_BRANCH_NAME);
        } catch (Throwable $x) {
            self::$volatile = null;
            throw $x;
        }
    }

    /**
     * @dataProvider provideCases
     */
    public function testGetPushState(Env $env, string $expectedResultingClass): void
    {
        $env = $env->withPlaceholders([
            static::PLACEHOLDER_PROJECT_DIRECTORY => static::$volatile->getPath(true),
            static::PLACEHOLDER_HEAD_PREV_SHA1 => static::$headPrevSHA1,
            static::PLACEHOLDER_HEAD_PREV_SHA1_12 => substr(static::$headPrevSHA1, 0, 12),
            static::PLACEHOLDER_HEAD_SHA1 => static::$headSHA1,
            static::PLACEHOLDER_HEAD_SHA1_12 => substr(static::$headSHA1, 0, 12),
        ]);
        $factory = new StateFactory($env);
        try {
            $result = $factory->getCurrentState();
        } catch (Exception $x) {
            $result = $x;
        }
        $this->assertInstanceOf($expectedResultingClass, $result);
        if ($result instanceof State\Push) {
            $this->assertSame(State::EVENT_PUSH, $result->getEvent());
            $this->assertSame(static::PULLREQUEST_BRANCH_NAME, $result->getBranch());
            $this->assertSame(static::$headSHA1, $result->getLastCommitSha1());
            if ($result instanceof State\PushWithoutBaseCommit) {
                $error = null;
                try {
                    $result->getBaseCommitSha1();
                } catch (Exception $x) {
                    $error = $x;
                }
                $this->assertInstanceOf(Exception\IncompleteEnvironmentException::class, $error);
                $error = null;
                try {
                    $result->getCommitRange();
                } catch (Exception $x) {
                    $error = $x;
                }
                $this->assertInstanceOf(Exception\IncompleteEnvironmentException::class, $error);
            } else {
                $this->assertSame(static::$headPrevSHA1, $result->getBaseCommitSha1());
                $this->assertSame(static::$headPrevSHA1 . '...' . static::$headSHA1, $result->getCommitRange());
            }
        }
    }

    public function provideCases(): array
    {
        $appVeyor = $this->getAppVeyorFullEnv();
        $githubActions = $this->getGithubActionsFullEnv();
        $travisCI = $this->getTravisCIFullEnv();

        return [
            [$appVeyor, State\PushWithoutBaseCommit::class],
            [$githubActions, State\PushWithoutBaseCommit::class],
            [$githubActions->with(['GITHUB_CONTEXT' => $this->getRelevantGitHubContextJson()]), State\Push::class],
            [$travisCI, State\Push::class],
        ];
    }

    private function getAppVeyorFullEnv(): Env
    {
        return new Env([
            'CI' => 'True',
            'APPVEYOR' => 'True',
            'APPVEYOR_ACCOUNT_NAME' => 'johndoe',
            'APPVEYOR_API_URL' => 'http://localhost:1033/',
            'APPVEYOR_BUILD_AGENT_HYPERV_NIC_CONFIGURED' => 'true',
            'APPVEYOR_BUILD_FOLDER' => static::PLACEHOLDER_PROJECT_DIRECTORY,
            'APPVEYOR_BUILD_ID' => '33733924',
            'APPVEYOR_BUILD_NUMBER' => '97',
            'APPVEYOR_BUILD_VERSION' => '1.0.97',
            'APPVEYOR_BUILD_WORKER_IMAGE' => 'Visual Studio 2015',
            'APPVEYOR_JOB_ID' => 'e9dp08hk6hxte84p',
            'APPVEYOR_JOB_NUMBER' => '1',
            'APPVEYOR_PROJECT_ID' => '690577',
            'APPVEYOR_PROJECT_NAME' => 'foobar',
            'APPVEYOR_PROJECT_SLUG' => 'foobar',
            'APPVEYOR_REPO_BRANCH' => static::PULLREQUEST_BRANCH_NAME,
            'APPVEYOR_REPO_COMMIT' => static::PLACEHOLDER_HEAD_SHA1,
            'APPVEYOR_REPO_COMMIT_AUTHOR' => static::AUTHOR_NAME,
            'APPVEYOR_REPO_COMMIT_AUTHOR_EMAIL' => static::AUTHOR_EMAIL,
            'APPVEYOR_REPO_COMMIT_MESSAGE' => static::LAST_HEAD_COMMIT_MESSAGE,
            'APPVEYOR_REPO_COMMIT_TIMESTAMP' => '2020-06-25T12:13:27.0000000Z',
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
            'GITHUB_BASE_REF' => '',
            'GITHUB_EVENT_NAME' => 'push',
            'GITHUB_EVENT_PATH' => '/home/runner/work/_temp/_github_workflow/event.json',
            'GITHUB_GRAPHQL_URL' => 'https://api.github.com/graphql',
            'GITHUB_HEAD_REF' => '',
            'GITHUB_JOB' => 'test',
            'GITHUB_REF' => 'refs/heads/' . static::PULLREQUEST_BRANCH_NAME,
            'GITHUB_REPOSITORY' => 'johndoe/foobar',
            'GITHUB_REPOSITORY_OWNER' => 'johndoe',
            'GITHUB_RUN_ID' => '147399440',
            'GITHUB_RUN_NUMBER' => '99',
            'GITHUB_SERVER_URL' => 'https://github.com',
            'GITHUB_SHA' => static::PLACEHOLDER_HEAD_SHA1,
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
            'TRAVIS_BRANCH' => static::PULLREQUEST_BRANCH_NAME,
            'TRAVIS_BUILD_DIR' => static::PLACEHOLDER_PROJECT_DIRECTORY,
            'TRAVIS_BUILD_ID' => '702003394',
            'TRAVIS_BUILD_NUMBER' => '95',
            'TRAVIS_BUILD_STAGE_NAME' => '',
            'TRAVIS_BUILD_WEB_URL' => 'https://travis-ci.org/johndoe/foobar/builds/702003394',
            'TRAVIS_CMD' => 'php test.php',
            'TRAVIS_COMMIT' => static::PLACEHOLDER_HEAD_SHA1,
            'TRAVIS_COMMIT_MESSAGE' => static::LAST_HEAD_COMMIT_MESSAGE,
            'TRAVIS_COMMIT_RANGE' => static::PLACEHOLDER_HEAD_PREV_SHA1_12 . '...' . static::PLACEHOLDER_HEAD_SHA1_12,
            'TRAVIS_CPU_ARCH' => 'amd64',
            'TRAVIS_DIST' => 'xenial',
            'TRAVIS_ENABLE_INFRA_DETECTION' => 'true',
            'TRAVIS_EVENT_TYPE' => 'push',
            'TRAVIS_HOME' => '/home/travis',
            'TRAVIS_INFRA' => 'gce',
            'TRAVIS_INIT' => 'systemd',
            'TRAVIS_INTERNAL_RUBY_REGEX' => '^ruby-(2\.[0-4]\.[0-9]|1\.9\.3)',
            'TRAVIS_JOB_ID' => '702003395',
            'TRAVIS_JOB_NAME' => '',
            'TRAVIS_JOB_NUMBER' => '95.1',
            'TRAVIS_JOB_WEB_URL' => 'https://travis-ci.org/johndoe/foobar/jobs/702003395',
            'TRAVIS_LANGUAGE' => 'php',
            'TRAVIS_OSX_IMAGE' => '',
            'TRAVIS_OS_NAME' => 'linux',
            'TRAVIS_PHP_VERSION' => '7.2',
            'TRAVIS_PRE_CHEF_BOOTSTRAP_TIME' => '2019-03-25T16:16:42',
            'TRAVIS_PULL_REQUEST' => 'false',
            'TRAVIS_PULL_REQUEST_BRANCH' => '',
            'TRAVIS_PULL_REQUEST_SHA' => '',
            'TRAVIS_PULL_REQUEST_SLUG' => '',
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
            'TRAVIS_TIMER_ID' => '10d907c0',
            'TRAVIS_TIMER_START_TIME' => "{$timestamp}123456789",
            'TRAVIS_TMPDIR' => '/tmp/tmp.OBjFiJ9pkp',
            'TRAVIS_UID' => '2000',
        ]);
    }

    private function getRelevantGitHubContextJson(): string
    {
        return json_encode([
            'event_name' => 'push',
            'event' => [
                'after' => static::PLACEHOLDER_HEAD_SHA1,
                'before' => static::PLACEHOLDER_HEAD_PREV_SHA1,
            ],
        ]);
    }
}
