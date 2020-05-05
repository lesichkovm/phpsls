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

function parseArgs($argv = null) {
    $argv = $argv ? $argv : $_SERVER['argv'];
    $o = ['arguments' => [], 'params' => []];

    array_shift($argv);
    $o = ['arguments' => [], 'parameters' => []];
    for ($i = 0, $j = count($argv); $i < $j; $i++) {
        $a = $argv[$i];
        if (substr($a, 0, 2) == '--') {
            $eq = strpos($a, '=');
            if ($eq !== false) {
                $o['parameters'][substr($a, 2, $eq - 2)] = substr($a, $eq + 1);
            } else {
                $k = substr($a, 2);
                if ($i + 1 < $j && $argv[$i + 1][0] !== '-') {
                    $o['parameters'][$k] = $argv[$i + 1];
                    $i++;
                } else if (!isset($o[$k])) {
                    $o['parameters'][$k] = true;
                }
            }
        } else if (substr($a, 0, 1) == '-') {
            if (substr($a, 2, 1) == '=') {
                $o['parameters'][substr($a, 1, 1)] = substr($a, 3);
            } else {
                foreach (str_split(substr($a, 1)) as $k) {
                    if (!isset($o[$k])) {
                        $o['parameters'][$k] = true;
                    }
                }
                if ($i + 1 < $j && $argv[$i + 1][0] !== '-') {
                    $o['parameters'][$k] = $argv[$i + 1];
                    $i++;
                }
            }
        } else {
            $o['arguments'][] = $a;
        }
    }
    return $o;
}