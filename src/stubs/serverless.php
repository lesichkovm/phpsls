<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/main.php';

function run($args = []) {
    date_default_timezone_set('Europe/London');

    \Sinevia\Serverless::openwhisk($args);

    return runResponseFormat(main());
}

function runResponseFormat($response) {
    if (is_object($response)) {
        $response = json_decode(json_encode($response), true);
    }
    if (is_array($response) AND isset($response['body'])) {
        return $response;
    } else {
        return ['body' => $response];
    }
}
