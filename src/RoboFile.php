<?php

namespace PHPServerless;

class RoboFile extends \Robo\Tasks
{

    public $dirCwd = null;
    public $dirPhpSls = null;
    public $dirConfig = null;
    public $dirPhpSlsDeploy = null;
    public $dirTests = null;
    public $fileEnv = null;
    public $fileConfigEnvironment = null;
    public $fileConfigTesting = null;
    public $fileMain = null;

    function __construct()
    {
        $this->_prepare();
    }

    private function _prepare()
    {
        $this->dirCwd = getcwd();
        $this->dirConfig = $this->dirCwd . DIRECTORY_SEPARATOR . 'config';
        $this->dirPhpSls = $this->dirCwd . DIRECTORY_SEPARATOR . '.phpsls';
        $this->dirPhpSlsDeploy = $this->dirPhpSls . DIRECTORY_SEPARATOR . 'deploy';
        $this->dirTests = $this->dirCwd . DIRECTORY_SEPARATOR . 'tests';
        
        $this->fileConfigTesting = $this->dirConfig . DIRECTORY_SEPARATOR . 'testing.php';
        $this->fileEnv = $this->dirCwd . DIRECTORY_SEPARATOR . 'env.php';
        $this->fileMain = $this->dirCwd . DIRECTORY_SEPARATOR . 'main.php';


        if (is_dir($this->dirPhpSls) == true) {
            return true;
        }

        \mkdir($this->dirPhpSls);

        if (is_dir($this->dirPhpSls) == true) {
            return $this->say('Failed ro create directory .phpsls in current directory. Please create manually.');
        }
    }

    /**
     * Initializes an environment
     */
    public function init($environment = "", $functionName = "")
    {
        /* 1. Environment */
        if ($environment == "") {
            $environment = trim($this->ask('What environment do you want to initialize (i.e local, staging, live)?'));
        }

        if ($environment == "") {
            $this->say("Environment cannot be empty. FAILED");
            return false;
        }

        /* 2. Function name */
        if ($environment != "local") {
            $functionName = trim($this->ask('What would you like your function to be called?'));

            if ($functionName == "") {
                $this->say("Function name cannot be empty. FAILED");
                return false;
            }
        }

        /* 3. Create stucture */
        $this->say('1. Creating config directry, if missing ...');

        if (\is_dir($this->dirConfig) == false) {
            \mkdir($this->dirConfig);
        }


        $this->say('2. Creating config file for "' . $environment . '" environment, if missing ...');

        $this->fileConfigEnvironment = $this->dirConfig . DIRECTORY_SEPARATOR . $environment . '.php';

        if (\file_exists($this->fileConfigEnvironment) == false) {
            $stub = $environment == "local" ? "config-local.php" : "config.php";
            $configFileContents = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . $stub);
            if ($environment != "local") {
                $configFileContents = \str_replace("{YOURFUNCTION}", $functionName, $configFileContents);
            }
            file_put_contents($this->fileConfigEnvironment, $configFileContents);
            $this->say("Configuration file for environment '" . $environment . "' created. SUCCESS");
            $this->say("Please check all is correct at: '" . $this->fileConfigEnvironment . "'");
        } else {
            $this->say("Configuration file for environment '" . $environment . "' already exists at " . $this->fileConfigEnvironment . ". SKIPPED");
        }

        $this->say('4. Creating main file, if missing...');

        if (\file_exists($this->fileMain) == false) {
            $mainFileContents = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'main.php');
            file_put_contents($this->fileMain, $mainFileContents);
            $this->say("Main file created. SUCCESS");
            $this->say("Please check all is correct at: '" . $this->fileMain . "'");
        } else {
            $this->say("Main file already exists at " . $this->fileMain . ". SKIPPED");
        }

        $this->say('5. Creating env file, if missing ...');

        if (\file_exists($this->fileEnv) == false) {
            $envFileContents = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'env.php');
            file_put_contents($this->fileEnv, $envFileContents);
            $this->say("Env file created. SUCCESS");
            $this->say("Please check all is correct at: '" . $this->fileEnv . "'");
        } else {
            $this->say("Env file already exists at " . $this->fileEnv . ". SKIPPED");
        }

        $this->say('6. Creating composer.json file, if missing ...');
        if (\file_exists($this->dirCwd.'/composer.json') == false) {
            $composerJson = [
                'require'=>[
                    "dg/composer-cleaner" => "v2.1",
                    "sinevia/php-library-serverless"=> "^1.9",     
                ],
                'require-dev'=>[
                    "lesichkovm/phpsls" => "^1.5",
                    "phpunit/phpunit"=> "8.5.1",
                ],
                "config"=> [
                    "optimize-autoloader"=> true,
                    "preferred-install"=> "dist",
                    "sort-packages"=> true,
                ],
                "minimum-stability"=> "dev",
                "prefer-stable"=> true,
                "extra"=> [
                    "cleaner-ignore"=> [
                        "phpunit/phpunit"=> true,
                        "vlucas/valitron"=> true,
                    ]
                ]
            ];
            file_put_contents($this->dirCwd.'/composer.json', \json_encode($composerJson,JSON_PRETTY_PRINT));
        }

        $this->say('7. Updating composer.json file ...');
        $composerJson = json_decode(file_get_contents($this->dirCwd.'/composer.json'), true);
        if($composerJson==null){
            $this->say("File \"composer.json\" missing. FAILED");
            return false;
        }
        $autoloadFiles = $composerJson['autoload']['files']??[];
        \array_unshift($autoloadFiles, "main.php"); // Second
        \array_unshift($autoloadFiles, "env.php");  // First
        $composerJson['autoload']['files']=\array_values(\array_unique($autoloadFiles));
        $composerJson['autoload']['psr-4']['App\\']="app/";
        $composerJson['autoload']['psr-4']['Tests\\']="tests/";
        $composerJson['require']["dg/composer-cleaner"] = "v2.1";
        $composerJson['require']["sinevia/php-library-serverless"] = "^1.7";
        $composerJson["extra"]["cleaner-ignore"]["phpunit/phpunit"]= true;
        $composerJson["extra"]["cleaner-ignore"]["vlucas/valitron"]= true;
        $composerJson["config"]["optimize-autoloader"] = true;
        $composerJson["config"]["preferred-install"]= "dist";
        $composerJson["config"]["sort-packages"]= true;
        $composerJson["minimum-stability"]= "dev";
        $composerJson["prefer-stable"]= true;
        file_put_contents($this->dirCwd.'/composer.json', \json_encode($composerJson,JSON_PRETTY_PRINT));
        
        // 8. Run composer (with dev)
        // $this->say('8. Updating composer dependencies...');

        // $isSuccessful = $this->taskExec('composer')
        //     ->arg('update')
        //     ->option('prefer-dist')
        //     ->option('optimize-autoloader')
        //     ->dir($this->dirCwd)
        //     ->run()->wasSuccessful();
        // if ($isSuccessful == false) {
        //     return $this->say('Failed.');
        // }

        $this->say('8. Please run "composer update" to update dependencies');

        return true;
    }

    /**
     * Deploys an environment to the serverless action
     * specified in its configuration file
     */
    public function deploy($environment)
    {
        // 1. Does the configuration file exists? No => Exit
        $this->say('1. Checking configuration...');

        $this->fileConfigEnvironment = $this->dirConfig . DIRECTORY_SEPARATOR . $environment . '.php';

        if (file_exists($this->fileConfigEnvironment) == false) {
            return $this->say('Configuration file for environment "' . $environment . '" missing at: ' . $this->fileConfigEnvironment);
        }

        if (file_exists($this->fileMain) == false) {
            return $this->say('Main file with function "main()" missing at: ' . $this->fileMain);
        }

        // 2. Load the configuration file for the enviroment
        $this->say('2. Loading configuratiion file for environment "' . $environment . '" ...');
        \Sinevia\Registry::set("ENVIRONMENT", $environment);
        $this->_loadEnvConf(\Sinevia\Registry::get("ENVIRONMENT"));

        // 3. Check if serverless function name is set
        $this->say('3. Checking if serverless function name set for environment "' . $environment . '" ...');
        $functionName = \Sinevia\Registry::get('SERVERLESS_FUNCTION_NAME', '');

        if ($functionName == "") {
            return $this->say('SERVERLESS_FUNCTION_NAME not set for environment "' . $environment . '"');
        } else {
            $this->say('SERVERLESS_FUNCTION_NAME is set as "' . $functionName . '"');
        }

        if ($functionName == "{YOUR_LIVE_SERVERLESS_FUNCTION_NAME}") {
            return $this->say('SERVERLESS_FUNCTION_NAME not correct for environment "' . $environment . '"');
        }

        // 3. Check if serverless provider is set
        $this->say('3. Checking if serverless provider is supported for "' . $environment . '" ...');
        $serverlessProvider = strtolower(\Sinevia\Registry::get('SERVERLESS_PROVIDER', 'ibm'));
        $supportedProviders = ['aws', 'ibm'];
        if (in_array($serverlessProvider, $supportedProviders) == false) {
            return $this->say('SERVERLESS_PROVIDER not supported "' . $serverlessProvider . '"');
        }

        $this->say('SERVERLESS_PROVIDER is set as "' . $serverlessProvider . '"');

        // 4. Create deployment directory
        $this->say('4. Creating deployment directory...');
        if (file_exists($this->dirPhpSlsDeploy) == false) {
            $isSuccessful = $this->taskExec('mkdir')
                ->arg($this->dirPhpSlsDeploy)
                ->run()
                ->wasSuccessful();
            if ($isSuccessful == false) {
                return $this->say('Failed.');
            }
        }

        $this->taskCleanDir([$this->dirPhpSlsDeploy])->run();

        // 5. Add required stub files
        $this->say('5. Copying stub files...');
        $serverlessFileContents = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'serverless.php');
        file_put_contents($this->dirPhpSlsDeploy . DIRECTORY_SEPARATOR . 'serverless.php', $serverlessFileContents);

        $serverlessConfigFile = 'serverless-ibm.yaml';
        if ($serverlessProvider == 'aws') {
            $serverlessConfigFile = 'serverless-aws.yaml';
        }
        $serverlessFileContents = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . $serverlessConfigFile);
        $serverlessFileContents = str_replace('{YOURFUNCTION}', $functionName, $serverlessFileContents);
        file_put_contents($this->dirPhpSlsDeploy . DIRECTORY_SEPARATOR . 'serverless.yaml', $serverlessFileContents);

        // 6. Copy project files
        $this->say('6. Copying files...');
        $this->taskCopyDir([getcwd() => $this->dirPhpSlsDeploy])
            ->exclude([
                $this->dirPhpSls,
                $this->dirCwd . DIRECTORY_SEPARATOR . 'composer.lock',
                $this->dirCwd . DIRECTORY_SEPARATOR . 'nbproject',
                $this->dirCwd . DIRECTORY_SEPARATOR . 'node_modules',
                $this->dirCwd . DIRECTORY_SEPARATOR . 'vendor',
            ])
            // ->option('function', $functionName) // Not working since Serverless v.1.5.1
            ->run();

        // 7. Run tests
        $this->say('7. Running tests...');
        $isSuccessful = $this->test();
        if ($isSuccessful == false) {
            return $this->say('Failed');
        }

        // 8. Run composer (no-dev)
        $this->say('8. Updating composer dependencies...');
        $isSuccessful = $this->taskExec('composer')
            ->arg('update')
            ->option('no-dev')
            ->option('prefer-dist')
            ->option('optimize-autoloader')
            ->dir($this->dirPhpSlsDeploy)
            ->run()->wasSuccessful();
        if ($isSuccessful == false) {
            return $this->say('Failed.');
        }

        // 9. Prepare for deployment
        $this->say('9. Prepare for deployment...');
        $this->taskReplaceInFile($this->dirPhpSlsDeploy . DIRECTORY_SEPARATOR . 'env.php')
            ->from('$environment = "local"; // !!! Do not change will be modified automatically during deployment')
            ->to('$environment = "' . $environment . '"; // !!! Do not change will be modified automatically during deployment')
            ->run();

        $packageFileContents = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'package.json');
        file_put_contents($this->dirPhpSlsDeploy . DIRECTORY_SEPARATOR . 'package.json', $packageFileContents);

        try {
            $this->say('5. NPM Install Packages...');
            $this->taskExec('npm')->arg('install')
                // ->option('function', $functionName) // Not working since Serverless v.1.5.1
                ->dir($this->dirPhpSlsDeploy)
                ->run();
        } catch (\Exception $e) {
            $this->say('There was an exception: ' . $e->getMessage());
        }

        // 10. Deploy
        try {
            $this->say('10. Deploying...');
            $this->taskExec('sls')
                ->arg('deploy')
                // ->option('function', $functionName) // Not working since Serverless v.1.5.1
                ->dir($this->dirPhpSlsDeploy)
                ->run();
        } catch (\Exception $e) {
            $this->say('There was an exception: ' . $e->getMessage());
            return;
        }

        // 11. Cleanup after deployment
        $this->say('11. Cleaning up...');

        // 12. Cleanup after deployment
        $this->say('12. Opening URL...');
        $this->open($environment);
    }

    /**
     * Runs the tests
     * @return boolean true if tests successful, false otherwise
     */
    public function test()
    {
        /* START: Reload enviroment */
        \Sinevia\Registry::set("ENVIRONMENT", 'testing');
        $this->_loadEnvConf(\Sinevia\Registry::get("ENVIRONMENT"));
        /* END: Reload enviroment */

        $testingFramework = \Sinevia\Registry::get('TESTING_FRAMEWORK', 'PHPUNIT'); // Options: TESTIFY, PHPUNIT, NONE

        if ($testingFramework == "") {
            return $this->say('TESTING_FRAMEWORK not set in file: ' . $this->dirConfig . DIRECTORY_SEPARATOR . 'testing.php');
        }

        if ($testingFramework == "TESTIFY") {
            return $this->testWithTestify();
        }

        if ($testingFramework == "PHPUNIT") {
            return $this->testWithPhpUnit();
        }

        return true;
    }

    /**
     * Testing with PHPUnit
     * @url https://phpunit.de/index.html
     * @return boolean true if tests successful, false otherwise
     */
    private function testWithPhpUnit()
    {
        $this->say('Running PHPUnit tests...');

        $isSuccessful = $this->taskExec('composer')
            ->arg('update')
            ->option('prefer-dist')
            ->option('optimize-autoloader')
            ->run()
            ->wasSuccessful();

        // 1. Run composer
        $isSuccessful = $this->taskExec('composer')
            ->arg('update')
            ->option('prefer-dist')
            ->option('optimize-autoloader')
            ->run()->wasSuccessful();

        if ($isSuccessful == false) {
            return false;
        }

        // 2. Run tests
        $isSuccessful = $this->taskExec('phpunit')
            ->dir('vendor/bin')
            ->option('configuration', '../../phpunit.xml')
            ->run()
            ->wasSuccessful();

        if ($isSuccessful == false) {
            return false;
        }

        return true;
    }

    /**
     * Testing with Testify
     * @url https://github.com/BafS/Testify.php
     * @return boolean true if tests successful, false otherwise
     */
    private function testWithTestify()
    {
        if (file_exists($this->dirTests . '/test.php') == false) {
            $this->say('Tests Skipped. Not test file at: ' . $this->dirTests. '/test.php');
            return true;
        }

        $this->say('Running tests...');

        $isSuccessful = $this->taskExec('composer')
            ->arg('update')
            ->option('prefer-dist')
            ->option('optimize-autoloader')
            ->run()
            ->wasSuccessful();

        $result = $this->taskExec('php')
            ->dir($this->dirTests)
            ->arg('test.php')
            ->printOutput(true)
            ->run();

        $output = trim($result->getMessage());

        if ($result->wasSuccessful() == false) {
            $this->say('Test Failed');
            return false;
        }

        if ($output == "") {
            $output = shell_exec('php "'.$this->dirTests.'/test.php"'); // Re-test, as no output on Linux Mint
            if (trim($output == "")) {
                $this->say('Tests Failed. No output');
                return false;
            }
        }

        if (strpos($output, 'Tests: [fail]') > -1) {
            $this->say('Tests Failed');
            return false;
        }

        $this->say('Tests Successful');

        return true;
    }

    public function migrate($environment)
    {
        $this->say('============= START: Migrations ============');

        // 1. Does the configuration file exists? No => Exit
        $this->say('1. Checking configuration...');

        $this->fileConfigEnvironment = $this->dirConfig . DIRECTORY_SEPARATOR . $environment . '.php';

        if (file_exists($this->fileConfigEnvironment) == false) {
            return $this->say('Configuration file for environment "' . $environment . '" missing at: ' . $this->fileConfigEnvironment);
        }

        if (file_exists($this->fileMain) == false) {
            return $this->say('Main file with function "main()" missing at: ' . $this->fileMain);
        }

        // 2. Load the configuration file for the enviroment
        \Sinevia\Registry::set("ENVIRONMENT", $environment);
        $this->_loadEnvConf(\Sinevia\Registry::get("ENVIRONMENT"));

        $this->say('3. Preparing for running migratons...');

        require_once 'app/functions.php';

        $this->say('4. Running migrations ...');
        $db = function_exists('eloquent') ? eloquent()->getConnection()->getPdo() : db();
        \Sinevia\Migrate::setDirectoryMigration(\Sinevia\Registry::get('DIR_MIGRATIONS'));
        \Sinevia\Migrate::setDatabase($db);
        \Sinevia\Migrate::$verbose = false;
        \Sinevia\Migrate::up();

        $this->say('5. Migrations finished ...');

        $this->say('============== END: Migrations =============');
    }

    public function open($environment)
    {
        /* START: Reload enviroment */
        \Sinevia\Registry::set("ENVIRONMENT", $environment);
        $this->_loadEnvConf(\Sinevia\Registry::get("ENVIRONMENT"));
        /* END: Reload enviroment */

        $url = \Sinevia\Registry::get('URL_BASE', '');
        if ($url == "") {
            return $this->say('URL_BASE not set for ' . $environment);
        }

        if (self::_isWindows()) {
            $isSuccessful = $this->taskExec('start')
                ->arg('firefox')
                ->arg($url)
                ->run();
        }
        if (self::_isWindows() == false) {
            $isSuccessful = $this->taskExec('firefox')
                ->arg($url)
                ->run();
        }
    }

    public function seed($environment, $className)
    {
        $this->say('============= START: Seed ============');

        // 1. Does the configuration file exists? No => Exit
        $this->say('1. Checking configuration...');

        $this->fileConfigEnvironment = $this->dirConfig . DIRECTORY_SEPARATOR . $environment . '.php';

        if (file_exists($this->fileConfigEnvironment) == false) {
            return $this->say('Configuration file for environment "' . $environment . '" missing at: ' . $this->fileConfigEnvironment);
        }

        if (file_exists($this->fileMain) == false) {
            return $this->say('Main file with function "main()" missing at: ' . $this->fileMain);
        }

        // 2. Load the configuration file for the enviroment
        \Sinevia\Registry::set("ENVIRONMENT", $environment);
        $this->_loadEnvConf(\Sinevia\Registry::get("ENVIRONMENT"));

        $this->say('3. Preparing for running seed...');

        $dirSeeds = \Sinevia\Registry::get("DIR_SEEDS");
        $classPath = $dirSeeds . DIRECTORY_SEPARATOR . $className . '.php';
        require_once $classPath;

        $this->say('4. Running seed ...');

        if (class_exists($className) == false) {
            return $this->say('Class "' . $className . '" DOES NOT EXIST at location ' . $classPath . ' . FAILED');
        }

        $seedInstance = new $className;

        if (method_exists($seedInstance, 'run') == false) {
            return $this->say('Class "' . $className . '" DOES NOT HAVE a "run" method . FAILED');
        }

        $seedInstance->run();

        $this->say('============== END: Seed =============');
    }

    /**
     * Serves the application locally using the PHP built-in server
     * @return void
     */
    public function serve()
    {
        /* START: Reload enviroment */
        \Sinevia\Registry::set("ENVIRONMENT", 'local');
        $this->_loadEnvConf(\Sinevia\Registry::get("ENVIRONMENT"));
        /* END: Reload enviroment */

        $url = \Sinevia\Registry::get('URL_BASE', '');
        if ($url == "") {
            return $this->say('URL_BASE not set for local');
        }

        $domain = str_replace(['http://', 'https://'], '', $url);
        if ($domain == "") {
            $domain = 'localhost:35555';
        }

        $serverFileContents = file_get_contents(__DIR__ . '/stubs/index.php');
        file_put_contents($this->dirPhpSls . DIRECTORY_SEPARATOR . 'index.php', $serverFileContents);

        $isSuccessful = $this
            ->taskOpenBrowser($url)
            ->taskExec('php')
            ->arg('-S')
            ->arg($domain)
            ->arg($this->dirPhpSls . DIRECTORY_SEPARATOR . 'index.php')
            ->run();
    }

    public function setup($feature) {
        if ($feature == "phpunit"){
            return $this->setupPhpUnit();
        }
    }

    private function setupPhpUnit(){
        $this->say('1. Creating "phpunit.xml" file, if missing ...');
        
        $filePhpUnit = $this->dirCwd . DIRECTORY_SEPARATOR . 'phpunit.xml';

        if (\file_exists($filePhpUnit) == false) {
            $filePhpUnitContents = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'phpunit.xml');
            file_put_contents($filePhpUnit, $filePhpUnitContents);
            $this->say('File "'.$filePhpUnit.'" created. SUCCESS');
        } else {
            $this->say('File "'.$filePhpUnit.'" already exists. SKIPPED');
        }

        $this->say('2. Creating "tests" directory, if missing ...');
        
        $dirTests = $this->dirCwd . DIRECTORY_SEPARATOR . 'tests';
        
        if (\is_dir($dirTests) == false) {
            \Sinevia\Native::directoryCreate($dirTests);
            $this->say('Directory "'.$dirTests.'" created. SUCCESS');
        } else {
            $this->say('Directory "'.$dirTests.'" already exists. SKIPPED');
        }

        $this->say('2. Creating "BaseTest.php" file, if missing ...');
        
        $fileBaseTest = $this->dirCwd . DIRECTORY_SEPARATOR . 'tests'  . DIRECTORY_SEPARATOR . 'BaseTest.php';
        
        if (\file_exists($fileBaseTest) == false) {
            $fileBaseTestContents = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'BaseTest.php');
            file_put_contents($fileBaseTest, $fileBaseTestContents);
            $this->say('File "'.$fileBaseTest.'" created. SUCCESS');
        } else {
            $this->say('File "'.$fileBaseTest.'" already exists. SKIPPED');
        }


        $this->say('3. Creating config file for "testing" environment, if missing ...');

        if (\file_exists($this->fileConfigTesting) == false) {
            $stub = "config-testing.php";
            $configFileContents = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . $stub);
            $configFileContents = str_replace('{{ TESTING_FRAMEWORK }}', "PHPUNIT", $configFileContents);
            file_put_contents($this->fileConfigTesting, $configFileContents);
            $this->say("Configuration file for environment 'testing' created. SUCCESS");
            $this->say("Please check all is correct at: '" . $this->fileConfigTesting . "'");
        } else {
            $this->say("Configuration file for environment 'testing' already exists at " . $this->fileConfigTesting . ". SKIPPED");
        }
        
        return true;
    }

    /**
     * Retrieves the logs from serverless
     */
    public function logs($environment)
    {
        /* START: Reload enviroment */
        \Sinevia\Registry::set("ENVIRONMENT", $environment);
        $this->_loadEnvConf(\Sinevia\Registry::get("ENVIRONMENT"));
        /* END: Reload enviroment */

        $functionName = \Sinevia\Registry::get('SERVERLESS_FUNCTION_NAME', '');
        if ($functionName == "") {
            return $this->say('SERVERLESS_FUNCTION_NAME not set for ' . $environment);
        }

        $this->taskExec('sls')
            ->arg('logs')
            ->option('function', $functionName)
            ->dir($this->dirPhpSlsDeploy)
            ->run();
    }

    /**
     * Loads the environment configuration variables
     * @param string $environment
     * @return void
     */
    private function _loadEnvConf($environment)
    {
        $envConfigFile = $this->dirConfig . DIRECTORY_SEPARATOR . $environment . '.php';

        if (file_exists($envConfigFile)) {
            $envConfigVars = include($envConfigFile);

            if (is_array($envConfigVars)) {
                foreach ($envConfigVars as $key => $value) {
                    \Sinevia\Registry::set($key, $value);
                }
            }
        }
    }

    /**
     * Checks whether the script runs on localhost
     * @return boolean
     */
    private static function _isLocal()
    {
        if (isset($_SERVER['REMOTE_ADDR']) == false) {
            return false;
        }

        $whitelist = array(
            '127.0.0.1',
            '::1'
        );

        if (in_array($_SERVER['REMOTE_ADDR'], $whitelist)) {
            return true;
        }

        false;
    }

    private static function _isWindows()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return true;
        }
        return false;
    }
}
