<?php
require __DIR__ . '/Helper.php';

class PhpSlsTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Helper::dirFsCreate();
    }

    public function testFsExists()
    {
        $this->assertTrue(is_dir(Helper::dirFs()));
    }

    public function testRoboFileExists()
    {

        $phpslsFilePath = dirname(__DIR__) . '/src/PhpSls.php';
        $this->assertTrue(file_exists($phpslsFilePath));
    }

    public function testInit()
    {
        $phpsls = Helper::phpslsPrepared();

        $isSuccess = $phpsls->init("local", "");

        $this->assertTrue($isSuccess);
        $this->assertTrue(is_dir($phpsls->dirConfig));
        $this->assertTrue(file_exists($phpsls->fileEnv));
        $this->assertTrue(file_exists($phpsls->fileMain));
        $this->assertTrue(file_exists($phpsls->dirCwd.'/composer.json'));
    }

    public function testSetupPhpUnit()
    {
        $phpsls = Helper::phpslsPrepared();

        $phpsls->init("local", "");
        $isSuccess = $phpsls->setup("phpunit");

        $this->assertTrue(file_exists($phpsls->dirCwd.'/phpunit.xml'));
        $this->assertTrue(is_dir($phpsls->dirCwd.'/tests'));
        $this->assertTrue(file_exists($phpsls->dirCwd.'/tests/BaseTest.php'));
    }

    public function tearDown(): void
    {
        Helper::dirFsDelete();
    }
}