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
            \Sinevia\Native::directoryCreate(self::dirFs());
        }
        return true;
    }

    public static function dirFsDelete()
    {
        \Sinevia\Native::directoryDeleteRecursive(self::dirFs());
    }

    /**
     * Imports and returns RoboFile ready for testing
     * @return \PHPServerless\RoboFile
     */
    public static function roboPrepared()
    {
        $roboFilePath = dirname(__DIR__) . '/src/RoboFile.php';
        require_once($roboFilePath);

        $robo = new \PHPServerless\RoboFile;
        
        $robo->dirCwd = Helper::dirFs();
        $robo->dirConfig = Helper::dirFs().'/config';
        $robo->dirPhpSls = Helper::dirFs().'/.phpsls';
        $robo->dirPhpSlsDeploy = $robo->dirPhpSls.'/deploy';
        $robo->fileEnv = $robo->dirCwd.'/env.php';
        $robo->fileMain = $robo->dirCwd.'/main.php';
        
        return $robo;
    }
}