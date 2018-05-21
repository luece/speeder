<?php
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Http\Request;
use Slim\Http\Response;

use Unframed\Handlers\Logger;
use Unframed\Handlers\Error;
use Unframed\Handlers\PhpError;
use Unframed\Handlers\NotFound;
use Unframed\Handlers\NotAllowed;
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
    public function run($displayError = true, $configPath = 'config/general_dev.php') 
    {
        session_start();
        require_once __DIR__ .'/../config/app.php';
        
        //set timezone
        date_default_timezone_set(TIME_ZONE);
        
        //read setting     
        $settings = $this->getSettings();
        $settings['displayErrorDetails'] = $displayError;

        // error handler
        error_reporting( $displayError ? E_ALL : 0);
        ini_set('display_errors', $displayError);
        ini_set('log_errors', 1);
        ini_set('error_log', $settings['logger']['path'] . date('md') . '-php.log');
        //ini_set('memory_limit','100M');
        
        // instantiation an Slim object
        $app = new App(['settings' => $settings]);

        // get container
        $this->container = $app->getContainer();

        //log handler
        $this->container['logger'] = function($container) {
            return new Logger($container);
        };

        // error handler
        $this->container['errorHandler'] = function ($container) {
            return new Error($container);
        };
        
        $this->container['phpErrorHandler'] = function ($container) {
            return new PhpError($container);
        };

        // notFoundHandler
        $this->container['notFoundHandler'] = function ($c) {
             return new NotFound;
        };

        // notAllowedHandler
        $this->container['notAllowedHandler'] = function ($c) {
            return new NotAllowed;
        };

        // CSRF protection middleware
        $this->container['csrf'] = function($container) {
            return new Guard;
        };
        
        $this->container['auth'] = function($container) {
            return new \App\Auth\Auth;
        };

        $this->container['flash'] = function($container) {
            return new \Slim\Flash\Messages;
        };

        $this->container['view'] = function ($container) {
            $settings  = $container->get('settings');
            $path = $settings['app']['views'];
            
            $view = new \Slim\Views\Twig($path, [ 'cache' => false,]);

            $view->addExtension(new \Slim\Views\TwigExtension(
                    $container->router, $container->request->getUri()
            ));

            $view->getEnvironment()->addGlobal('auth', [
                'check' => $container->auth->check(),
                'user'  => $container->auth->user()
            ]);

            $view->getEnvironment()->addGlobal('flash', $container->flash);

            return $view;
        };

        /*
          $this->container['validator'] = function ($container) {
          return new App\Validation\Validator;
          };

          $this->container['HomeController'] = function($container) {
          return new \App\Controllers\HomeController($container);
          };

          $this->container['AuthController'] = function($container) {
          return new \App\Controllers\Auth\AuthController($container);
          };

          $this->container['PasswordController'] = function($container) {
          return new \App\Controllers\Auth\PasswordController($container);
          };
         */

        $app->add($this->container->csrf);

        //v::with('App\\Validation\\Rules\\');

        /*
        $app->get('/[{name}]', function (Request $request, Response $response, array $args) {
            // Routes
            $abc = 'fdsa';
//$this['abc']($abc);
            // Sample log message
            //var_dump($this->get('settings')['logger']);
            //$this->logger->info("Slim-Skeleton '/' route");
            throw new \Exception('fdsa');
            // Render index view
            //var_dump($args);
        });
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

        $app->any('/', function ($request, $response, $args) {
            throw new \Exception('testf');
        });*/
        //router
        $this->container['routerHandler'] = function ($container) {
            return new \Unframed\Core\Router;
        };
        
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


        /*
          //数据库配置
          try {
          $dotenv = (new \Dotenv\Dotenv(__DIR__ . '/../'))->load();
          } catch (\Dotenv\Exception\InvalidPathException $e) {
          //
          }

          //数据库配置
          $capsule = new \Illuminate\Database\Capsule\Manager;

          $capsule->addConnection([
          'driver'    => getenv('DB_DRIVER'),
          'host'      => getenv('DB_HOST'),
          'database'  => getenv('DB_DATABASE'),
          'username'  => getenv('DB_USERNAME'),
          'password'  => getenv('DB_PASSWORD'),
          'charset'   => 'utf8',
          'port'      => getenv('DB_PORT'),
          'collation' => 'utf8_unicode_ci',
          'prefix'    => ''
          ]);

          $capsule->setAsGlobal();
          $capsule->bootEloquent();

          $this->container['db'] = function ($container) use ($capsule) {
          return $capsule;
          };
         */

        /*
          $app->add(new \App\Middleware\ValidationErrorsMiddleware($this->container));
          $app->add(new \App\Middleware\OldInputMiddleware($this->container));
          $app->add(new \App\Middleware\CsrfViewMiddleware($this->container));
         */

/*
//参数存储
$pc['app_id']='pimple';
//单例存储
$pc['app']=function($c){
    $app=new stdClass();
    $app->app_id=$c['app_id'];
    return $app;
};
$app_one=$pc['app'];
$app_two=$pc['app'];
var_dump($app_one === $app_two);// true
非单例存储
$pc['api'] = $pc->factory(function ($c) {
    $api=new stdClass();
    $api->app_id=$c['app_id'];
    return $api;
});
$api_one=$pc['api'];
$api_two=$pc['api'];
var_dump($api_two === $api_two);// false
//存储匿名函数
$container['random_func']=$container->protect(function ($a) use ($container) {
    $container['app_id'] = $container['app_id'] + 1;
    echo $container['app_id'];
});
//获取匿名函数
$random=$container['random_func'];
$container['random_func']('a');
echo $container['app_id'] . 'aaa';
$container['random_func']('b');
echo $container['app_id']. 'bbb';
$random('c');

 * 
 */
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
            'name'   => ucwords($settings['router']['application']),
            'path'   => BASE_PATH . 'src/' . $settings['app']['name'] . '/',
            'res'    => BASE_PATH . 'src/' . $settings['app']['name'] . '/Resources/',
            'config' => BASE_PATH . 'src/' . $settings['app']['name'] . '/Resources/config/',
            'lang'   => BASE_PATH . 'src/' . $settings['app']['name'] . '/Resources/lang/',
            'views'  => BASE_PATH . 'src/' . $settings['app']['name'] . '/Resources/views/',
        ];
        
        $configFile = $settings['app']['config'] . 'settings.php';
        if (is_file($configFile)) {
            $config = include $configFile;
            if (is_array($config)) {
                $settings = array_merge($settings, $config);
            }
        }
        return $settings;
    }


}