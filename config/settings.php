<?php
return [
    // Allow the web server to send the content-length header
    'addContentLengthHeader' => false, 
    //相对于 BASE_PATH
    'logStatus'              => true,
    'logPath'                => 'public/data/logs/',
    'dataPath'               => 'public/data/data/',
    'configPath'             => 'public/data/config/'
];

/*
SITE_DEFAULT_APPLICATION: post
SITE_DEFAULT_NAMESPACE: post
SITE_DEFAULT_CLASS: index
SITE_DEFAULT_METHOD: index
SITE_DEFAULT_ROUTER:
    ROUTER_NAME_POST:
        HOST: ''
        URL: (.+?)/([^/]+?)/([^/]+?)/([^/]+?)(/$|$)
        NAMESPACE: $1
        CLASS: $2
        METHOD: $3
        POST: $4
        FILE: ""
    ROUTER_NAME_NULL:
        HOST: ''
        URL: (.+?)/(.+?)/([^/]+?)(/$)
        NAMESPACE: $1
        CLASS: $2
        METHOD: $3
        POST: ""
        FILE: ""
    ROUTER_CLASS_POST:
        HOST: ''
        URL: (.+?)/(.+?)/([^/]+?)($)
        NAMESPACE: ""
        CLASS: $1
        METHOD: $2
        POST: $3
        FILE: ""
    ROUTER_CLASS_NULL:
        HOST: ''
        URL: ([^/]+?)/([^/]+?)(/$)
        NAMESPACE: ""
        CLASS: $1
        METHOD: $2
        POST: ""
        FILE: ""
    ROUTER_METHOD_POST:
        HOST: ''
        URL: ([^/]+?)/([^/]+?)($)
        NAMESPACE: ""
        CLASS: ""
        METHOD: $1
        POST: $2
        FILE: ""
    ROUTER_METHOD_NULL:
        HOST: ''
        URL: ([^/]+?)(/)(/$|$)
        NAMESPACE: ""
        CLASS: ""
        METHOD: $1
        POST: ""
        FILE: ""


*/
