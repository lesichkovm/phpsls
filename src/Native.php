<?php

namespace PHPServerless;

class Native {

    public static $logEcho = false;
    public static $logFile = '';
    public static $lastExecOut = []; // Latest output lines from exec

    public static function commandExists($command) {
        if (self::isWindows()) {
            $fp = \popen("where $command", "r");
            $result = \fgets($fp, 255);
            if (trim($result) == "") {
                return false;
            }
            $exists = !\preg_match('#Could not find files#', $result);
            \pclose($fp);
        } else { # non-Windows
            $fp = \popen("which $command", "r");
            $result = \fgets($fp, 255);
            if (trim($result) == "") {
                return false;
            }
            $exists = !empty($result);
            \pclose($fp);
        }

        return $exists;
    }

    public static function fileDelete($filePath) {
        return unlink($filePath);
    }

    /**
     * Replaces text in file matching a regular expression
     * Works also with array of strings and matching replacements
     */
    public static function fileReplaceText($filePath, $string, $replacement) {
        $contents = file_get_contents($filePath);
        if (\is_array($string)) {
            foreach ($string as $index => $s) {
                $out = preg_replace($s[$index], $replacement[$index], $contents);
            }
        } else {
            $out = str_replace($string, $replacement, $contents);
        }
        return file_put_contents($filePath, $out) === false ? false : true;
    }

    /**
     * Replaces text in file matching a regular expression
     * Works also with array of regular expressions and matching replacements
     */
    public static function fileReplaceTextRegex($filePath, $regex, $replacement) {
        $contents = file_get_contents($filePath);
        if (\is_array($regex)) {
            foreach ($regex as $index => $r) {
                $out = preg_replace($r[$index], $replacement[$index], $contents);
            }
        } else {
            $out = preg_replace($regex, $replacement, $contents);
        }
        return file_put_contents($filePath, $out) === false ? false : true;
    }

    /**
     * Creates a directory
     * @return boolean
     */
    public static function directoryCreate($directoryPath) {
        if (self::isWindows()) {
            $cmd = 'mkdir "' . $directoryPath . '"';
        } else {
            $cmd = '\mkdir --parents "' . $directoryPath . '"';
        }
        self::exec($cmd);

        if (\is_dir($directoryPath) == false) {
            return false;
        }

        return true;
    }

    public static function directoryClean($directoryPath) {
        if (is_string($directoryPath) == false) {
            return false;
        }
        self::directoryDeleteRecursive($directoryPath);
        self::directoryCreate($directoryPath);
    }

    /**
     * Recursively copies a directory into another
     * The target directory must exist or call with the force parameter
     */
    public static function directoryCopyRecursive($sourceDirectoryPath, $destinationDirectoryPath, $force = false) {
        if ($force == true) {
            self::directoryCreate($destinationDirectoryPath);
        }
        if (self::isWindows()) {
            return self::directoryCopyRecursiveWindows($sourceDirectoryPath, $destinationDirectoryPath);
        } else {
            return self::directoryCopyRecursiveLinux($sourceDirectoryPath, $destinationDirectoryPath);
        }
    }

    /**
     * Recursively merges a directory
     */
    public static function directoryMergeRecursive($sourceDirectoryPath, $destinationDirectoryPath) {
        if (self::isWindows()) {
            return self::directoryMergeRecursiveWindows($sourceDirectoryPath, $destinationDirectoryPath);
        } else {
            return self::directoryMergeRecursiveLinux($sourceDirectoryPath, $destinationDirectoryPath);
        }
    }

    /**
     * Recursively deletes a directory
     */
    public static function directoryDeleteRecursive($directoryPath) {
        if (self::isWindows()) {
            return self::directoryDeleteRecursiveWindows($directoryPath);
        } else {
            return self::directoryDeleteRecursiveLinux($directoryPath);
        }
    }

    /**
     * Executes a command
     */
    public static function exec($command) {
        self::log(' - Executing command: "' . $command . '"');

        self::$lastExecOut = "";

        exec($command, $out, $return);

        self::$lastExecOut = $out;

        return $return == 0 ? true : false;
    }

    public static function isLinux() {
        if (strtoupper(PHP_OS) === 'LINUX') {
            return true;
        }

        return false;
    }

    public static function isOsx() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'OSX') {
            return true;
        }

        return false;
    }

    public static function isWindows() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return true;
        }

        return false;
    }

    private static function directoryCopyRecursiveLinux($sourceDirectoryPath, $destinationDirectoryPath) {
        // remove trailing slashes to not create doubles
        $sourceDirectoryPathFixed = rtrim($sourceDirectoryPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $destinationDirectoryPathFixed = rtrim($destinationDirectoryPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '';

        // the backslash ignores any bash aliases
        $cmd = '\cp -Rp "' . $sourceDirectoryPathFixed . '"* "' . $destinationDirectoryPathFixed . '"';
        return self::exec($cmd);
    }

    private static function directoryCopyRecursiveWindows($sourceDirectoryPath, $destinationDir) {
        $sourceDirectoryPathFixed = str_replace('/', DIRECTORY_SEPARATOR, $sourceDirectoryPath);
        $destinationDirFixed = str_replace('/', DIRECTORY_SEPARATOR, $destinationDir);
        $cmd = 'xcopy "' . $sourceDirectoryPathFixed . '" "' . $destinationDirFixed . '" /s /e /h /y';
        return self::exec($cmd);
    }

    private static function directoryDeleteRecursiveLinux($directoryPath) {
        $cmd = '\rm -rf "' . $directoryPath . '"';
        return self::exec($cmd);
    }

    private static function directoryDeleteRecursiveWindows($directoryPath) {
        $directoryPathFixed = str_replace('/', DIRECTORY_SEPARATOR, $directoryPath);
        $cmd = 'rmdir "' . $directoryPathFixed . '" /s /q';
        return self::exec($cmd);
    }

    private static function directoryMergeRecursiveLinux($sourceDirectoryPath, $destinationDirectoryPath) {
        // remove trailing slashes to not create doubles
        $sourceDirectoryPathFixed = rtrim($sourceDirectoryPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $destinationDirFixed = rtrim($destinationDirectoryPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        $cmd = '\rsync -a "' . $sourceDirectoryPathFixed . '" "' . $destinationDirFixed . '"';
        return self::exec($cmd);
    }

    private static function directoryMergeRecursiveWindows($sourceDirectoryPath, $destinationDirectoryPath) {
        return self::directoryCopyRecursiveWindows($sourceDirectoryPath, $destinationDirectoryPath);
    }

    private static function log($message) {
        if (is_array($message)) {
            foreach ($message as $msg) {
                self::log($msg);
            }
            return;
        }

        $message = date('Y-m-d H:i:s: ') . $message . "\n";

        if (self::$logEcho == true) {
            echo $message;
        }

        if (self::$logFile != "") {
            file_put_contents(self::$logFile, $message, FILE_APPEND);
        }
    }

    /**
     * Returns the user's home directory.
     * @returns string|null
     */
    public static function userHome() {
        $home = getenv('HOME');

        if (!empty($home)) {
            // home should never end with a trailing slash.
            $home = rtrim($home, '/');
        } elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
            // home on windows
            $home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
            // If HOMEPATH is a root directory the path can end with a slash. Make sure
            // that doesn't happen.
            $home = rtrim($home, '\\/');
        }

        // Running in cygwin. Not 100% reliable
        if (strpos($home, 'cygwin') >= 0) {
            self::exec('cygpath -w --desktop');
            if (empty(self::$lastExecOut) == false) {
                return dirname(implode("\n", self::$lastExecOut));
            }
        }

        return empty($home) ? NULL : $home;
    }

}
