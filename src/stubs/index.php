<?php

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);

require_once dirname(__DIR__) . '/vendor/autoload.php';

$response = main();

if (is_string($response)) {
    die($response);
}

if (is_array($response) AND isset($response['body']) == true) {
    $response = $response['body'];
    if (is_string($response)) {
        die($response);
    }
    die(json_encode($response));
} else {
    die(json_encode($response));
}