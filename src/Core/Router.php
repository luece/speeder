<?php
namespace Unframed\Core;

class Router
{

    public function __invoke($request, $response, $args)
    {
        echo 'test';
        var_dump($this);
    }

}
