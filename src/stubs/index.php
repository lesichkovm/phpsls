<?php

require_once dirname(__DIR__) . '/index.php';

$response = main();
if (isset($response['body']) == true) {
    $response = $response['body'];
    if(is_string($response)){
        die($response);
    }
    die(json_encode($response));
} else {
    die(json_encode($response));
}