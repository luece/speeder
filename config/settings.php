<?php
return [
    // Allow the web server to send the content-length header
    'addContentLengthHeader' => false,
    //相对于 BASE_PATH
    'logger'                 => [
        'name' => 'Unframed',
        'path' => 'public/data/logs/',
    ],
    'router'                => [
        'application' => 'post',
        'controller'  => 'index',
        'action'      => 'index',
    ],
    'dataPath'               => 'public/data/data/',
    'configPath'             => 'public/data/config/',

    
];