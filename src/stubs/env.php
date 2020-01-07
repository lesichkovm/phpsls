<?php

/* AUTO */
$environment = "local"; // !!! Do not change will be modified automatically during deployment
\Sinevia\Registry::setIfNotExists("ENVIRONMENT", $environment);

/* TESTING */
\Sinevia\Registry::setIfNotExists("TESTING_FRAMEWORK", 'TESTIFY'); // Options: TESTIFY, PHPUNIT, NONE

/* FILE SYSTEM */
\Sinevia\Registry::setIfNotExists("DIR_BASE", __DIR__);
// \Sinevia\Registry::setIfNotExists("DIR_APP", __DIR__ . '/app');
// \Sinevia\Registry::setIfNotExists("DIR_MIGRATIONS_DIR", __DIR__ . '/app/database/migrations/');

/* 
 * CONFIGURATION
 * The configuration file allows you to add variables speecific for each environment
 * These are located in /config
 */
$envConfigFile = __DIR__ . '/config/' . $environment . '.php';
if(file_exists($envConfigFile)) {
    $envConfigVars = include(__DIR__ . '/config/' . $environment . '.php');
    
    if(is_array($envConfigVars)){
        foreach ($envConfigVars as $key => $value) {
            \Sinevia\Registry::setIfNotExists($key, $value);
        }
    }
}