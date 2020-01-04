<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/main.php';


function run($args = [])
{
    date_default_timezone_set('Europe/London');

    \Sinevia\Serverless::openwhisk($args);
    
    return responseHtml(main());
}

function responseHtml($html)
{
    return ['body' => $html];
}