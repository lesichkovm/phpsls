<?php

namespace PHPServerless;

trait PhpSlsEnvTrait {

    /**
     * Deploys an environment to the serverless action
     * specified in its configuration file
     * Params:
     * --dry-run=yes
     */
    public function dot($args, $params = []) {
        Native::$logEcho = true;
        $environment = trim(array_shift($args));
        $dryRun = (trim($params['dry-run'] ?? 'no') == 'yes');
        $force = (trim($params['force'] ?? 'no') == 'yes');

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

        //echo $this->fileDotEnvDynamic;

        $dotenv = \Dotenv\Dotenv::createMutable($this->dirCwd, [basename($this->fileDotEnvDynamic)]);
        $dotenv->load();

        foreach ($_ENV as $key => $value) {
            $resolvedValue = $this->_valueResolve($value, $environment);
            $_ENV[$key] = $resolvedValue;
        }

        if ($dryRun == true) {
            \Sinevia\Utils::alert($_ENV);
            return true;
        }

        if (file_exists($this->fileDotEnv)) {
            if ($force == "yes") {
                $this->say("File " . $this->fileDotEnv . " already exists but force option caller. OVERWRITING");
            } else {
                $this->say("File " . $this->fileDotEnv . " already exists. FAILED");
                return false;
            }
        }

        $envContent = '';
        foreach ($_ENV as $key => $value) {
            $envContent .= $key . '=' . json_encode($value) . "\n";
        }

        $length = file_put_contents($this->fileDotEnv, $envContent);
        if ($length !== false) {
            $this->say("File " . $this->fileDotEnv . " written successfully. SUCCESS");
            return true;
        }

        $this->say("File " . $this->fileDotEnv . " failed to be written check permissions. FAILED");
        return false;
    }

    function _createDotFileForEnvironment($environment, $destinationFilePath) {
        if (file_exists($this->fileDotEnvDynamic) == false) {
            $this->say("File .env.dynamic DOES NOT exist. FAILED");
            return false;
        }
        $dotenv = \Dotenv\Dotenv::createMutable($this->dirCwd, [basename($this->fileDotEnvDynamic)]);
        $dotenv->load();

        foreach ($_ENV as $key => $value) {
            $resolvedValue = $this->_valueResolve($value, $environment);
            $_ENV[$key] = $resolvedValue;
        }

        $envContent = '';
        foreach ($_ENV as $key => $value) {
            $envContent .= $key . '=' . json_encode($value) . "\n";
        }


        $length = file_put_contents($destinationFilePath, $envContent);
        if ($length !== false) {
            $this->say("File " . $destinationFilePath . " written successfully. SUCCESS");
            return true;
        }

        $this->say("File " . $destinationFilePath . " failed to be written check permissions. FAILED");
        return false;
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

        $cmd = "aws ssm get-parameter --with-decryption --name $ssmPath --region eu-west-2";
        //Native::$logEcho = true;
        $result = Native::exec($cmd);
        $value = json_decode(implode('', Native::$lastExecOut))->Parameter->Value ?? null;

        if (is_null($value)) {
            $this->say('Error: Amazon SSM key "' . $ssmPath . '" NOT FOUND. FAILED');
            exit(1);
        }

        return $value;
    }

    function _getFilePath($str) {
        $filePath = substr(substr($str, 7), 0, -1);

        if (strpos($filePath, ':') == 1) { // Windows drive letter?
            $filePath = implode('___!!!___', explode(':', $filePath, 2));
        }

        $param = \Sinevia\StringUtils::rightFrom($filePath, ":");
        $file = \Sinevia\StringUtils::leftFrom($filePath, ":");
        $file = str_replace('___!!!___', ':', $file);

        if (\Sinevia\StringUtils::startsWith($file, '~')) { // User home?
            $file = str_replace('~', Native::userHome(), $file);
        }

        if (file_exists($file) == false) {
            $this->say('ERROR: Environment file "' . $file . '" DOES NOT exist. FAILED');
            exit(1);
        }

        $fileContents = file_get_contents($file);
        $json = json_decode($fileContents, true);

        $dot = new \Adbar\Dot($json);

        $value = $dot->get($param);

        return $value;
    }

    function _valueResolve($value, $environment) {
        if (\Sinevia\StringUtils::hasSubstring($value, '$ENVIRONMENT')) {
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

}
