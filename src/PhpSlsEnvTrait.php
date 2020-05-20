<?php

namespace PHPServerless;

trait PhpSlsEnvTrait {

    /**
     * Deploys an environment to the serverless action
     * specified in its configuration file
     */
    public function dot($args, $params = []) {
        Native::$logEcho = true;
        $environment = trim(array_shift($args));

        /* 1. Environment */
        if ($environment == "") {
            $environment = trim($this->ask('What environment do you want to generate .env file for (i.e local, staging, live)?'));
        }

        if ($environment == "") {
            $this->say("Environment cannot be empty. FAILED");
            return false;
        }

        if (file_exists($this->fileDotEnvDynamic) == false) {
            $this->say("File .env.dynamic DOES NOT exist. FAILED");
            return false;
        }

        $dotenv = \Dotenv\Dotenv::createMutable($this->dirCwd, [basename($this->fileDotEnvDynamic)]);
        $dotenv->load();

        var_dump($_ENV);
        foreach ($_ENV as $key => $value) {
            $resolvedValue = $this->_valueResolve($value, $environment);
            $_ENV[$key] = $resolvedValue;
        }
        var_dump($_ENV);
    }
    
    function _isFileVariable($str) {
        if (\Sinevia\StringUtils::startsWith($str, '${file:') AND \Sinevia\StringUtils::endsWith($str, '}')) {
            return true;
        }
        return false;
    }

    function _isSsmVariable($str) {
        if (\Sinevia\StringUtils::startsWith($str, '${ssm:') AND \Sinevia\StringUtils::endsWith($str, '}')) {
            return true;
        }
        return false;
    }

    function _getSsmPath($ssmVariable) {
        $ssmPath = substr(substr($ssmVariable, 6), 0, -1);
        return $ssmPath;
    }
    
    function _getFilePath($str) {
        $filePath = substr(substr($str, 7), 0, -1);
        
        $param = \Sinevia\StringUtils::rightFrom($filePath, ":");
        $file = \Sinevia\StringUtils::leftFrom($filePath, ":");
        
        $fileContents = file_get_contents($file);
        $json = json_decode($fileContents);
        
        \Sinevia\Utils::alert($json);
        
        return $filePath;
    }

    function _valueResolve($value, $environment) {
        if(\Sinevia\StringUtils::hasSubstring($value, '$ENVIRONMENT')){
            $value = str_replace('$ENVIRONMENT', $environment, $value);
        }
        if ($this->_isSsmVariable($value)) {
            $value = $this->_getSsmPath($value);
        }
        if ($this->_isFileVariable($value)) {
            $value = $this->_getFilePath($value);
        }
        return $value;
    }

    //$cmd = "aws ssm get-parameter --with-decryption --name /test/db/name --region eu-west-2";
//PHPServerless\Native::$logEcho = true;
//$result = PHPServerless\Native::exec($cmd);
//var_dump(json_decode(implode('', PHPServerless\Native::$lastExecOut))->Parameter->Value);
}
