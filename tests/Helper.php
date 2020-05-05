<?php

class Helper
{
    public static function dirFs()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'fs';
    }

    public static function dirFsCreate()
    {
        if (is_dir(self::dirFs()) == false) {
            \PHPServerless\Native::directoryCreate(self::dirFs());
        }
        return true;
    }

    public static function dirFsDelete()
    {
        \PHPServerless\Native::directoryDeleteRecursive(self::dirFs());
    }

    /**
     * Imports and returns RoboFile ready for testing
     * @return \PHPServerless\RoboFile
     */
    public static function phpslsPrepared()
    {
        $roboFilePath = dirname(__DIR__) . '/src/PhpSls.php';
        require_once($roboFilePath);

        $phpsls = new \PHPServerless\PhpSls;

        $phpsls->dirCwd = Helper::dirFs();
        $phpsls->dirConfig = Helper::dirFs().'/config';
        $phpsls->dirPhpSls = Helper::dirFs().'/.phpsls';
        $phpsls->dirPhpSlsDeploy = $phpsls->dirPhpSls.'/deploy';
        $phpsls->fileConfigTesting = $phpsls->dirConfig.'/testing.php';
        $phpsls->fileEnv = $phpsls->dirCwd.'/env.php';
        $phpsls->fileMain = $phpsls->dirCwd.'/main.php';
        
        return $phpsls;
    }
}