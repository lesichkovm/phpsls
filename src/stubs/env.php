<?php

/* AUTO */
$environment = "local"; // !!! Do not change will be modified automatically during deployment
\Sinevia\Registry::setIfNotExists("ENVIRONMENT", $environment);

/* FILE SYSTEM */
\Sinevia\Registry::setIfNotExists("DIR_BASE", __DIR__);
\Sinevia\Registry::setIfNotExists("DIR_APP", __DIR__ . DIRECTORY_SEPARATOR . 'app');
\Sinevia\Registry::setIfNotExists("DIR_CONFIG", __DIR__ . DIRECTORY_SEPARATOR . 'config');
\Sinevia\Registry::setIfNotExists("DIR_MIGRATIONS", __DIR__  . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations');
\Sinevia\Registry::setIfNotExists("DIR_SEEDS", __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seeds');

/*
 * LOAD ENVIRONMENT CONFIGURATIONS
 * The configuration files allow to add variables specific for each environment
 * These are located in /config
 */
$envConfigFile = \Sinevia\Registry::get('DIR_CONFIG') . DIRECTORY_SEPARATOR . $environment . '.php';
\Sinevia\Serverless::loadFileToRegistry($envConfigFile);