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

        $roboFilePath = dirname(__DIR__) . '/src/RoboFile.php';
        require_once($roboFilePath);

        $robo = new \PHPServerless\RoboFile;

        $isSuccess = $robo->init("local", "", "none");

        $this->assertTrue($isSuccess);
    }

    public function tearDown(): void
    {
        Helper::dirFsDelete();
    }
}