<?php

/* AUTO */
$environment = "local"; // !!! Do not change will be modified automatically during deployment
\Sinevia\Registry::setIfNotExists("ENVIRONMENT", $environment);

/* FILE SYSTEM */
\Sinevia\Registry::setIfNotExists("DIR_BASE", __DIR__);
// \Sinevia\Registry::setIfNotExists("DIR_APP", __DIR__ . '/app');
// \Sinevia\Registry::setIfNotExists("DIR_MIGRATIONS_DIR", __DIR__ . '/app/database/migrations/');

/* FILE SYSTEM */
\Sinevia\Registry::setIfNotExists("DIR_BASE", __DIR__);
\Sinevia\Registry::setIfNotExists("DIR_APP", __DIR__ . '/app');
\Sinevia\Registry::setIfNotExists("DIR_CONFIG", __DIR__ . '/config');
\Sinevia\Registry::setIfNotExists("DIR_MIGRATIONS", __DIR__ . '/app/database/migrations/');
\Sinevia\Registry::setIfNotExists("USE_ELOQUENT", true);

/*
 * LOAD ENVIRONMENT CONFIGURATIONS
 * The configuration files allow to add variables specific for each environment
 * These are located in /config
 */
loadEnvConf(\Sinevia\Registry::get('ENVIRONMENT'));


/* REQUIRED FUNCTIONS */

/**
 * Loads the environment configuration variables
 * @param string $environment
 * @return void
 */
function loadEnvConf($environment) {
    $envConfigFile = \Sinevia\Registry::get('DIR_CONFIG') . DIRECTORY_SEPARATOR . $environment . '.php';

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
function isLocal() {
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
