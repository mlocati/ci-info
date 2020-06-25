<?php

declare(strict_types=1);

use CIInfo\Driver\AppVeyor;
use CIInfo\Driver\GithubActions;
use CIInfo\Driver\TravisCI;
use CIInfo\DriverList;
use CIInfo\Env;
use CIInfo\Exception\DirectoryNotFoundException;
use CIInfo\Exception\DuplicatedDriverHandleException;
use CIInfo\Exception\MultipleApplicableDriversException;
use PHPUnit\Framework\TestCase;

class DriverListTest extends TestCase
{
    public function testRegisterBuiltinDrivers()
    {
        $expectedLoadedDrivers = count(glob(CIINFO_TEST_ROOTDIR . '/src/Driver/*.php'));
        $driverList = new DriverList();
        $loadedDrivers = count($driverList->getDrivers());
        $this->assertGreaterThan(0, $loadedDrivers);
        $this->assertSame($expectedLoadedDrivers, $loadedDrivers);

        $this->assertInstanceOf(AppVeyor::class, $driverList->getDriverByHandle(AppVeyor::HANDLE));
        $this->assertInstanceOf(GithubActions::class, $driverList->getDriverByHandle(GithubActions::HANDLE));
        $this->assertInstanceOf(TravisCI::class, $driverList->getDriverByHandle(TravisCI::HANDLE));
    }

    public function testRegisteringDuplicatedDrivers()
    {
        $driverList = new DriverList(false);
        $driverList->registerBuiltinDrivers();
        $this->expectException(DuplicatedDriverHandleException::class);
        $driverList->registerBuiltinDrivers();
    }

    public function testRegisteringInvalidDirectory()
    {
        $driverList = new DriverList(false);
        $this->expectException(DirectoryNotFoundException::class);
        $driverList->registerDriversInDirectory(__DIR__ . '/not existing', 'Foo\\Bar');
    }

    public function testNoDrivers()
    {
        $driverList = new DriverList();
        $this->assertNull($driverList->getDriverForEnvironment(new Env([])));
    }

    public function provideMultipleApplicableDrivers(): array
    {
        return [
            [
                new Env(['APPVEYOR' => 'True', 'GITHUB_ACTIONS' => 'true']),
                [AppVeyor::HANDLE, GithubActions::HANDLE],
            ],
            [
                new Env(['APPVEYOR' => 'True', 'TRAVIS' => 'true']),
                [AppVeyor::HANDLE, TravisCI::HANDLE],
            ],
            [
                new Env(['GITHUB_ACTIONS' => 'true', 'TRAVIS' => 'true']),
                [GithubActions::HANDLE, TravisCI::HANDLE],
            ],
            [
                new Env(['APPVEYOR' => 'True', 'GITHUB_ACTIONS' => 'true', 'TRAVIS' => 'true']),
                [AppVeyor::HANDLE, GithubActions::HANDLE, TravisCI::HANDLE],
            ],
        ];
    }

    /**
     * @dataProvider provideMultipleApplicableDrivers
     */
    public function testMultipleApplicableDrivers(Env $env, array $duplicatedDriverHandles)
    {
        $driverList = new DriverList();
        $error = null;
        try {
            $driverList->getDriverForEnvironment($env);
        } catch (Throwable $x) {
            $error = $x;
        }
        $this->assertInstanceOf(MultipleApplicableDriversException::class, $error);
        sort($duplicatedDriverHandles);
        $this->assertSame(array_values($duplicatedDriverHandles), $error->getApplicableDriverHandles());
    }
}
