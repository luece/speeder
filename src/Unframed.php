<?php

use Slim\App;
use Unframed\Core\ServicesProvider;
use Unframed\Core\Environment;
use Respect\Validation\Validator as v;

/**
 * Unframed
 * @author 黑豆  <eaeb@qq.com>
 * @link https://unframed.cc/
 * @version 1.0.180515
 * @example
 *
 */
class Unframed
{

    protected $container;

    /**
     * program entry
     * @param int $displayError display error details, true for display
     */
    public function run($displayError = true)
    {
        session_start();
        require_once __DIR__ . '/../config/app.php';

        //set timezone
        date_default_timezone_set(TIME_ZONE);

        //read setting     
        $settings = $this->getSettings();
        $settings['displayErrorDetails'] = $displayError;

        // error handler
        error_reporting($displayError ? E_ALL : 0);
        ini_set('display_errors', $displayError);
        ini_set('log_errors', 1);
        ini_set('error_log', $settings['logger']['path'] . date('md') . '-php.log');
        //ini_set('memory_limit','100M');
        // instantiation an Slim object
        $app = new App(['settings' => $settings]);

        // get container
        $this->container = $app->getContainer();

        // register default services
        $servicesProvider = new ServicesProvider();
        $servicesProvider->register($this->container);

        if ($this->container->has('csrf')) {
            $app->add($this->container->csrf);
        }

        //v::with('App\\Validation\\Rules\\');

        /*
          中间件
          $app->get('/', function ($request, $response, $args) {
          $response->getBody()->write(' Hello ');

          return $response;
          });
          $app->add(function ($request, $response, $next) {
          $response->getBody()->write('BEFORE');
          $response = $next($request, $response);
          $response->getBody()->write('AFTER');

          return $response;
          });
          $app->add(new \App\Middleware\ValidationErrorsMiddleware($this->container));
          $app->add(new \App\Middleware\OldInputMiddleware($this->container));
          $app->add(new \App\Middleware\CsrfViewMiddleware($this->container));
         */


        //if ()

        $handler = 'routerHandler';
        if ($this->container->has($handler)) {
            $params = [$app];

            $callable = $this->container->get($handler);
            // Call the registered handler
            call_user_func_array($callable, $params);
        }

        //$app->get('/aaa', \Unframed\Core\Logger::class . ':home')
        $app->run();
        return $app;
    }

    /**
     * Call relevant handler from the Container if needed. If it doesn't exist,
     * then just re-throw.
     *
     * @param  Throwable $e
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface $response
     * @return ResponseInterface
     * @throws Throwable
     */
    protected function handleRoute(Throwable $e, ServerRequestInterface $request, ResponseInterface $response)
    {
        $handler = 'routeHandler';
        $params = [$request, $response, $e];

        if ($this->container->has($handler)) {
            $callable = $this->container->get($handler);
            // Call the registered handler
            return call_user_func_array($callable, $params);
        }

        // No handlers found, so just throw the exception
        throw $e;
    }

    /**
     * 
     */
    protected function getSettings()
    {
        $settings = include SYS_PATH . 'config/settings.php';
        $settings['cli'] = PHP_SAPI == 'cli' ? true : false;
        $settings['logger']['path'] = BASE_PATH . $settings['logger']['path'] . date('Ym') . '/';
        $settings['bootstrap'] = BASE_PATH . $settings['bootstrap'];

        $hostFile = $settings['bootstrap'] . 'host.php';
        if (is_file($hostFile)) {
            $host = include $hostFile;
            // match host
            if (is_array($host) && !empty($host)) {
                // get hostname
                //$hostname = ($hostname === 'localhost') && isset($_SERVER ['argv'][1]) ? $_SERVER ['argv'][1] : $hostname;
                $hostname = Environment::getHost();

                foreach ($host as $key => $arr) {
                    $arr ['host'] = str_replace('.', '\.', $arr ['host']);
                    $arr ['host'] = str_replace(':int', '[0-9]+?', $arr ['host']);
                    $arr ['host'] = str_replace(':str', '.+?', $arr ['host']);
                    $arr ['host'] = str_replace(':', '.', $arr ['host']);
                    if (preg_match('#^' . $arr ['host'] . '$#', $hostname)) {
                        // read 
                        $router = $settings['router'];
                        $router['application'] = isset($arr ['application']) ? $arr ['application'] : $router['application'];
                        $router['controller'] = isset($arr ['controller']) ? $arr ['controller'] : $router['controller'];
                        $router['action'] = isset($arr ['action']) ? $arr ['action'] : $router['action'];
                        $settings['router'] = $router;
                        break;
                    }
                }
            }
        }

        $settings['app'] = [
            'name'      => ucwords($settings['router']['application']),
            'path'      => BASE_PATH . 'src/' . ucwords($settings['router']['application']) . '/',
            'res'       => BASE_PATH . 'src/' . ucwords($settings['router']['application']) . '/Resources/',
            'bootstrap' => BASE_PATH . 'src/' . ucwords($settings['router']['application']) . '/Resources/bootstrap/',
            'views'     => BASE_PATH . 'src/' . ucwords($settings['router']['application']) . '/Resources/views/',
        ];

        $configFile = $settings['app']['bootstrap'] . 'settings.php';
        if (is_file($configFile)) {
            $config = include $configFile;
            if (is_array($config)) {
                $settings = array_merge($settings, $config);
            }
        }
        return $settings;
    }

}
