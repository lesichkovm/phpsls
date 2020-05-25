<?php

namespace PHPServerless;

trait PhpSlsDeployTrait {

    /**
     * Deploys an environment to the serverless action
     * specified in its configuration file
     */
    public function deploy($args, $params = []) {
        Native::$logEcho = true;

        $environment = array_shift($args);

        /* 1. Environment */
        $this->say('1. Checking environment...');
        if ($this->deployTaskCheckEnvironment($environment) == false) {
            $this->say("Environment check failed. FAILED");
            return false;
        }


        // 2. Create deployment directory
        $this->say('2. Creating deployment directory...');
        if ($this->taskCreateDir($this->dirPhpSlsDeploy) == false) {
            $this->say("Deployment directory failed to be created. FAILED");
            return false;
        }

        $this->say('3. Cleaning deployment directory...');
        $this->taskCleanDir($this->dirPhpSlsDeploy);

        $envFile = $this->dirPhpSlsDeploy . '/.env';

        $this->say('4 Reading environment variables for environment "' . $environment . '" ...');
        $env = $this->_env($environment);

        // 3. Check if serverless function name is set
        $this->say('3. Checking if serverless function name set for environment "' . $environment . '" ...');
        $functionName = $env['SERVERLESS_FUNCTION_NAME'] ?? '';
        $serverlessProvider = strtolower($env['SERVERLESS_PROVIDER'] ?? '');

        if ($functionName == "") {
            return $this->say('SERVERLESS_FUNCTION_NAME not set for environment "' . $environment . '"');
        } else {
            $this->say('SERVERLESS_FUNCTION_NAME is set as "' . $functionName . '"');
        }

        if ($functionName == "{YOUR_LIVE_SERVERLESS_FUNCTION_NAME}") {
            return $this->say('SERVERLESS_FUNCTION_NAME not correct for environment "' . $environment . '"');
        }

        // 3. Check if serverless provider is set
        $this->say('4. Checking if serverless provider is supported for "' . $environment . '" ...');
        $supportedProviders = ['aws', 'ibm'];
        if (in_array($serverlessProvider, $supportedProviders) == false) {
            return $this->say('SERVERLESS_PROVIDER not supported "' . $serverlessProvider . '"');
        }

        $this->say('SERVERLESS_PROVIDER is set as "' . $serverlessProvider . '"');

        // 5. Add required stub files
        $this->say('5. Copying stub files...');
        if($this->deployTaskCopyStubFiles($serverlessProvider, $functionName)==false){
            $this->say("Stub files failed to be created. FAILED");
            return false;
        }

        // 6. Copy project files
        $this->say('6. Copying files...');
        Native::directoryCopyRecursive(getcwd(), $this->dirPhpSlsDeploy);

        // 7. Remove unneeded files
        $this->say('7. Copying files...');
        if ($this->deployTaskRemoveUnneededFilesAndDirectories()) {
            $this->say("Failed to remove unneeded files and directories. FAILED");
            return false;
        }

        // 2. Load the configuration file for the enviroment
        $this->say('8. Creating .env file for environment "' . $environment . '" ...');
        $this->_createDotFileForEnvironment($environment, $envFile);



        // 7. Run tests
//        $this->say('7. Running tests...');
//        $isTestSuccessful = $this->test();
//        if ($isTestSuccessful == false) {
//            return $this->say('Failed');
//        }
        // 8. Run composer (no-dev)
        $this->say('8. Updating composer dependencies...');
        if (chdir($this->dirPhpSlsDeploy)) {
            $isSuccessful = Native::exec('composer update --no-dev --prefer-dist --optimize-autoloader');
            if ($isSuccessful == false) {
                return $this->say('Failed.');
            }
        }

        // 9. Prepare for deployment
        $this->say('9. Prepare for deployment...');
        //Native::fileReplaceText($this->dirPhpSlsDeploy . DIRECTORY_SEPARATOR . 'env.php', '$environment = "local"; // !!! Do not change will be modified automatically during deployment', '$environment = "' . $environment . '"; // !!! Do not change will be modified automatically during deployment');

        $packageFileContents = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'package.json');
        file_put_contents($this->dirPhpSlsDeploy . DIRECTORY_SEPARATOR . 'package.json', $packageFileContents);

        try {
            $this->say('5. NPM Install Packages...');
            if (chdir($this->dirPhpSlsDeploy)) {
                Native::exec('npm install');
            }
        } catch (\Exception $e) {
            $this->say('There was an exception: ' . $e->getMessage());
            return false;
        }

        // 10. Deploy
        try {
            $this->say('10. Deploying...');
            if (chdir($this->dirPhpSlsDeploy)) {
                Native::exec('sls deploy');
            }
        } catch (\Exception $e) {
            $this->say('There was an exception: ' . $e->getMessage());
            return false;
        }

        // 11. Cleanup after deployment
        $this->say('11. Cleaning up...');

        // 12. Cleanup after deployment
        $this->say('12. Opening URL...');
        $this->open([$environment], []);
    }

    private function taskCreateDir($dirPath) {
        if (is_dir($dirPath) == true) {
            return true;
        }
        if (file_exists($dirPath) == true) {
            $this->say('Path ALREADY EXISTS as a file');
            return false;
        }

        $isSuccessful = Native::directoryCreate($this->$dirPath);

        return $isSuccessful;
    }

    private function deployTaskCheckEnvironment($environment) {
        if ($environment == "") {
            $environment = trim($this->ask('What environment do you want to deploy (i.e staging, live)?'));
        }

        if ($environment == "") {
            $this->say("Environment cannot be empty. FAILED");
            return false;
        }

        if ($environment == "local") {
            $this->say('Environment "local" cannot be deployed. FAILED');
            return false;
        }

        return true;
    }

    private function deployTaskCopyStubFiles($serverlessProvider, $functionName) {
        // serverless.php
        $stubFilePath = __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'serverless.php';
        $serverlessFilePath = $this->dirPhpSlsDeploy . DIRECTORY_SEPARATOR . 'serverless.php';
        $isCopied = Native::fileCopy($stubFilePath, $serverlessFilePath);

        if ($isCopied == false) {
            return false;
        }

        // serverless.yaml
        $serverlessConfigFile = 'serverless-ibm.yaml';
        if ($serverlessProvider == 'aws') {
            $serverlessConfigFile = 'serverless-aws.yaml';
        }
        $stubFilePath = __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . $serverlessConfigFile;
        $serverlessYamlFilePath = $this->dirPhpSlsDeploy . DIRECTORY_SEPARATOR . 'serverless.yaml';
        $serverlessYamlFileContents = file_get_contents($stubFilePath);
        $serverlessYamlFileContentsWithFunction = str_replace('{YOURFUNCTION}', $functionName, $serverlessYamlFileContents);
        $result = file_put_contents($serverlessYamlFilePath, $serverlessYamlFileContentsWithFunction);
        if ($result === false) {
            return false;
        }
        if (file_exists($serverlessYamlFilePath)) {
            return true;
        }
        return false;
    }

    /**
     * Removes unneeded files and directories
     * @return boolean
     */
    private function deployTaskRemoveUnneededFilesAndDirectories() {
        $files = [
            $this->dirPhpSlsDeploy . '/.env',
            $this->dirPhpSlsDeploy . '/composer.lock',
        ];

        $dirs = [
            $this->dirPhpSlsDeploy . '/.git',
            $this->dirPhpSlsDeploy . '/nbproject',
            $this->dirPhpSlsDeploy . '/node_modules',
            $this->dirPhpSlsDeploy . '/vendor',
        ];

        foreach ($files as $file) {
            Native::fileDelete($file);
        }

        foreach ($dirs as $dir) {
            Native::directoryDeleteRecursive($dir);
        }

        foreach ($files as $file) {
            if (file_exists($file)) {
                return false;
            }
        }

        foreach ($dirs as $dir) {
            if (file_exists($dir)) {
                return false;
            }
        }

        return false;
    }

}
