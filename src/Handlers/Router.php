<?php
namespace Unframed\Handlers;

/**
 * 
 */
class Router
{

    /**
     * 
     * @param type $app
     */
    public function __invoke($app)
    {
        $app->any('/[{application}[/{controller}[/{action}]]]', function ($request, $response, $args) {
            $router = $this->get('settings')['router'];

            //application
            $application = isset($router['application']) ? $router['application'] : '';
            $application = isset($args['application']) ? $args['application'] : $application;
            $application = ucwords($application);

            //controller
            $controller = isset($router['controller']) ? $router['controller'] : '';
            $controller = isset($args['controller']) ? $args['controller'] : $controller;
            $controller = ucwords($controller) . 'Controller';

            //action
            $action = isset($router['action']) ? $router['action'] : '';
            $action = isset($args['action']) ? $args['action'] : $router['action'];
            $action = $action . 'Action';

            $class = $application . '\\Controller\\' . $controller;
            $object = null;
            if (class_exists($class)) {
                $object = new $class($this);
            }

            if (is_callable([$object, $action])) {
                return call_user_func_array([$object, $action], [$request, $response, $args]);
                //return $object->$action([$request, $response, $args]);
            } else {
                $notFoundHandler = $this->get('notFoundHandler');
                return $notFoundHandler($request, $response);
            }
        });
        return $app;
    }

}
