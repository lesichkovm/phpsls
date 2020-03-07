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
}