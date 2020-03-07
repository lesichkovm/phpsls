<?php
namespace PHPServerless;

/**
 * Loads the environment configuration variables
 * @param string $environment
 * @return void
 */
function loadEnvironmentConfigurations($environment) {
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