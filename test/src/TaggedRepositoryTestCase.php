<?php

declare(strict_types=1);

namespace CIInfo\Test;

use CIInfo\Env;
use CIInfo\Git;
use CIInfo\State;
use CIInfo\State\Tag;
use CIInfo\StateFactory;
use Throwable;

abstract class TaggedRepositoryTestCase extends RepositoryTestCase
{
    /**
     * @var string
     */
    protected const TAG_NAME = 'tag.name';

    /**
     * @var string
     */
    protected const ANNOTATED_TAG_MESSAGE = 'Message associated to the annotated tag';

    /**
     * @var string|null
     */
    protected static $annotatedTagSha1;

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
            if (static::isAnnotatedTag()) {
                $git->run('tag -a -m ' . escapeshellarg(static::ANNOTATED_TAG_MESSAGE) . ' ' . static::TAG_NAME);
                self::$annotatedTagSha1 = $git->run('show-ref -s refs/tags/' . static::TAG_NAME)[0];
            } else {
                $git->run('tag ' . static::TAG_NAME);
            }
            $git->run('checkout master');
        } catch (Throwable $x) {
            self::$volatile = null;
            throw $x;
        }
    }

    /**
     * @dataProvider provideCases
     */
    public function testGetTagState(Env $env, string $expectedExcetion = ''): void
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
            $this->assertInstanceOf(Tag::class, $state);
            $this->assertSame(State::EVENT_TAG, $state->getEvent());
            $this->assertSame(static::$headSHA1, $state->getLastCommitSha1());
            $this->assertSame(static::TAG_NAME, $state->getTag());
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
            [$appVeyor],
            [$githubActions],
            [$travisCI],
        ];
    }

    abstract protected static function isAnnotatedTag(): bool;

    private function getAppVeyorFullEnv(): Env
    {
        return new Env([
            'CI' => 'True',
            'CI_LINUX' => 'False',
            'CI_WINDOWS' => 'True',
            'APPVEYOR' => 'True',
            'APPVEYOR_ACCOUNT_NAME' => 'johndoe',
            'APPVEYOR_API_URL' => 'http://localhost:1033/',
            'APPVEYOR_BUILD_AGENT_HYPERV_NIC_CONFIGURED' => 'true',
            'APPVEYOR_BUILD_FOLDER' => static::PLACEHOLDER_PROJECT_DIRECTORY,
            'APPVEYOR_BUILD_ID' => '33730883',
            'APPVEYOR_BUILD_NUMBER' => '94',
            'APPVEYOR_BUILD_VERSION' => '1.0.94',
            'APPVEYOR_BUILD_WORKER_IMAGE' => 'Visual Studio 2015',
            'APPVEYOR_JOB_ID' => 'x1i3yjfjt1q437by',
            'APPVEYOR_JOB_NUMBER' => '1',
            'APPVEYOR_PROJECT_ID' => '690577',
            'APPVEYOR_PROJECT_NAME' => 'foobar',
            'APPVEYOR_PROJECT_SLUG' => 'foobar',
            'APPVEYOR_REPO_BRANCH' => static::isAnnotatedTag() ? static::TAG_NAME : static::PULLREQUEST_BRANCH_NAME,
            'APPVEYOR_REPO_COMMIT' => static::PLACEHOLDER_HEAD_SHA1,
            'APPVEYOR_REPO_COMMIT_AUTHOR' => static::AUTHOR_NAME,
            'APPVEYOR_REPO_COMMIT_AUTHOR_EMAIL' => static::AUTHOR_EMAIL,
            'APPVEYOR_REPO_COMMIT_MESSAGE' => self::LAST_HEAD_COMMIT_MESSAGE,
            'APPVEYOR_REPO_COMMIT_TIMESTAMP' => '2020-06-25T09:37:14.0000000Z',
            'APPVEYOR_REPO_NAME' => 'johndoe/foobar',
            'APPVEYOR_REPO_PROVIDER' => 'gitHub',
            'APPVEYOR_REPO_SCM' => 'git',
            'APPVEYOR_REPO_TAG' => 'true',
            'APPVEYOR_REPO_TAG_NAME' => static::TAG_NAME,
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
            'GITHUB_EVENT_NAME' => 'create',
            'GITHUB_EVENT_PATH' => '/home/runner/work/_temp/_github_workflow/event.json',
            'GITHUB_GRAPHQL_URL' => 'https://api.github.com/graphql',
            'GITHUB_HEAD_REF' => '',
            'GITHUB_JOB' => 'test',
            'GITHUB_REF' => 'refs/tags/' . static::TAG_NAME,
            'GITHUB_REPOSITORY' => 'johndoe/foobar',
            'GITHUB_REPOSITORY_OWNER' => 'johndoe',
            'GITHUB_RUN_ID' => '147250233',
            'GITHUB_RUN_NUMBER' => '95',
            'GITHUB_SERVER_URL' => 'https://github.com',
            'GITHUB_SHA' => static::PLACEHOLDER_HEAD_SHA1,
            'GITHUB_WORKFLOW' => 'Test',
            'GITHUB_WORKSPACE' => '/home/runner/work/foobar/foobar',
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
            'TRAVIS_BRANCH' => static::TAG_NAME,
            'TRAVIS_BUILD_DIR' => '/home/travis/build/johndoe/foobar',
            'TRAVIS_BUILD_ID' => '701963730',
            'TRAVIS_BUILD_NUMBER' => '92',
            'TRAVIS_BUILD_STAGE_NAME' => '',
            'TRAVIS_BUILD_WEB_URL' => 'https://travis-ci.org/johndoe/foobar/builds/701963730',
            'TRAVIS_CMD' => 'php test.php',
            'TRAVIS_COMMIT' => static::PLACEHOLDER_HEAD_SHA1,
            'TRAVIS_COMMIT_MESSAGE' => static::LAST_HEAD_COMMIT_MESSAGE,
            'TRAVIS_COMMIT_RANGE' => '',
            'TRAVIS_CPU_ARCH' => 'amd64',
            'TRAVIS_DIST' => 'xenial',
            'TRAVIS_ENABLE_INFRA_DETECTION' => 'true',
            'TRAVIS_EVENT_TYPE' => 'push',
            'TRAVIS_HOME' => '/home/travis',
            'TRAVIS_INFRA' => 'unknown',
            'TRAVIS_INIT' => 'systemd',
            'TRAVIS_INTERNAL_RUBY_REGEX' => '^ruby-(2\.[0-4]\.[0-9]|1\.9\.3)',
            'TRAVIS_JOB_ID' => '701963731',
            'TRAVIS_JOB_NAME' => '',
            'TRAVIS_JOB_NUMBER' => '92.1',
            'TRAVIS_JOB_WEB_URL' => 'https://travis-ci.org/johndoe/foobar/jobs/701963731',
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
            'TRAVIS_TAG' => static::TAG_NAME,
            'TRAVIS_TEST_RESULT' => '',
            'TRAVIS_TIMER_ID' => '03850c40',
            'TRAVIS_TIMER_START_TIME' => "{$timestamp}123456789",
            'TRAVIS_TMPDIR' => '/tmp/tmp.9pxiu9h1lo',
            'TRAVIS_UID' => '2000',
        ]);
    }
}
