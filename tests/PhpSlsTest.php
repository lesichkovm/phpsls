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

        $roboFilePath = dirname(__DIR__) . '/src/RoboFile.php';
        $this->assertTrue(file_exists($roboFilePath));
    }

    public function testInit()
    {
        $robo = Helper::roboPrepared();

        $isSuccess = $robo->init("local", "");

        $this->assertTrue($isSuccess);
        $this->assertTrue(is_dir($robo->dirConfig));
        $this->assertTrue(file_exists($robo->fileEnv));
        $this->assertTrue(file_exists($robo->fileMain));
        $this->assertTrue(file_exists($robo->dirCwd.'/composer.json'));
    }

    public function testSetupPhpUnit()
    {
        $robo = Helper::roboPrepared();

        $robo->init("local", "");
        $isSuccess = $robo->setup("phpunit");

        $this->assertTrue(file_exists($robo->dirCwd.'/phpunit.xml'));
        $this->assertTrue(is_dir($robo->dirCwd.'/tests'));
        $this->assertTrue(file_exists($robo->dirCwd.'/tests/BaseTest.php'));
    }

    public function tearDown(): void
    {
        Helper::dirFsDelete();
    }
}