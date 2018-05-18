<?php
use Slim\App;
use Slim\Csrf\Guard;
use Unframed\Core\Logger;
use Unframed\Core\Error;

use Respect\Validation\Validator as v;
// Register routes
use Slim\Http\Request;
use Slim\Http\Response;

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

    /**
     * program entry
     * @param array $param 调用控制器参数
     *         array(
     *             'application' => ''  // 文件夹/命名空间 ，不能为空
     *             'controller'  => ''  // 控制器，不能为空
     *             'method'      => ''  // 方法，默认为index
     *         )
     * @param int $displayError display error details, true for display
     */
    public function run($param = array(), $displayError = true, $configPath = 'config/general_dev.php') 
    {
        session_start();
        require_once __DIR__ .'/../config/app.php';
        
        //set timezone
        date_default_timezone_set(TIME_ZONE);

        //read setting
        $settings = include SYS_PATH . 'config/settings.php';
        $settings['cli'] = PHP_SAPI == 'cli' ? true : false;
        $settings['logger']['path'] = BASE_PATH . $settings['logger']['path'] . date('Ym') . '/';
        $settings['displayErrorDetails'] = $displayError;
        


        // error handler
        ini_set('display_errors', $displayError);
        error_reporting( $displayError ? E_ALL : 0);
        ini_set('log_errors', 1);
        ini_set('error_log', $settings['logger']['path'] . date('md') . '-php.log');
        //ini_set('memory_limit','100M');

        // instantiation an Slim object
        $app = new App(['settings' => $settings]);

        // get container
        $container = $app->getContainer();
        
        

        //外来路由控制替换
        if (is_array($param)) {
            $rewrite = $param;
        } elseif (is_string($param)) {
            $rewrite = explode(',', $param);
            //交换数组中的键和值。
            $rewrite = array_flip($rewrite);
            //使用键名比较计算数组的交集。
            $rewrite = array_intersect_key($this->container->getParameter('VIRTUALHOST'), $rewrite);
        } else {
            $rewrite = $this->container->getParameter('VIRTUALHOST');
        }

        // 匹配域名
        if ($rewrite) {
            // 获取地址栏域名
            $hostname = $container['request']->getUri()->getHost();
            //$hostname = ($hostname === 'localhost') && isset($_SERVER ['argv'][1]) ? $_SERVER ['argv'][1] : $hostname;

            // 根据域名host匹配路由规则
            foreach ($rewrite as $key => $arr) {
                $arr ['host'] = str_replace('.', '\.', $arr ['host']);
                $arr ['host'] = str_replace(':int', '[0-9]+?', $arr ['host']);
                $arr ['host'] = str_replace(':str', '.+?', $arr ['host']);
                $arr ['host'] = str_replace(':', '.', $arr ['host']);
                if (preg_match('#^' . $arr ['host'] . '$#', $hostname)) {
                    // 读取路由规则，并读取匹配路由的默认命名空间和类，错误模板文件夹
                    $defaultApp = $arr ['app'];
                    $defaultName = $arr ['name'];
                    $defaultClass = $arr ['class'];

                    $router = isset($arr ['router']) ? $arr ['router'] : array();
                    break;
                }
            }
        }
        $post = BASE_PATH . 'src/Post/Resources/';
        
        
        //log handler
        $container['logger'] = function($container) {
            return new Logger($container);
        };

        //error handler
        $container['phpErrorHandler'] = $container['errorHandler'] = function ($container) {
            if ($container->get('settings')['displayErrorDetails']) {
                return (new Error($container))->get();
            } else {
                return new Error($container);
            }
        };
        
        $container['notFoundHandler'] = function ($c) {
            return function ($request, $response) use ($c) {
                return $c['response']
                                ->withStatus(404)
                                ->withHeader('Content-Type', 'text/html')
                                ->write('Page not foundfff');
            };
        };
/*
        $container['notAllowedHandler'] = function ($c) {
            return function ($request, $response, $methods) use ($c) {
                return $c['response']
                                ->withStatus(405)
                                ->withHeader('Allow', implode(', ', $methods))
                                ->withHeader('Content-type', 'text/html')
                                ->write('Method must be one of: ' . implode(', ', $methods));
            };
        };
*/
        $container['auth'] = function($container) {
            return new \App\Auth\Auth;
        };

        $container['flash'] = function($container) {
            return new \Slim\Flash\Messages;
        };

        $container['view'] = function ($container) use ($post) {
            $view = new \Slim\Views\Twig($post . 'views/', [
                'cache' => false,
            ]);

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
          $container['validator'] = function ($container) {
          return new App\Validation\Validator;
          };

          $container['HomeController'] = function($container) {
          return new \App\Controllers\HomeController($container);
          };

          $container['AuthController'] = function($container) {
          return new \App\Controllers\Auth\AuthController($container);
          };

          $container['PasswordController'] = function($container) {
          return new \App\Controllers\Auth\PasswordController($container);
          };
         */

        // CSRF protection middleware
        $container['csrf'] = function($container) {
            return new Guard;
        };
        $app->add($container->csrf);

        //v::with('App\\Validation\\Rules\\');
        //require __DIR__ . '/../app/routes.php';


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
         */

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
         * 
         */


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
                return $object->$action($request, $response, $args);
            } else {
                return $response
                                ->withStatus(404)
                                ->withHeader('Content-Type', 'text/html')
                                ->write('Page not found');
            }
        });
        $app->run();
        return $app;

        /*


//$app->get('/aaa', \Unframed\Core\Logger::class . ':home');
*/

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

          $container['db'] = function ($container) use ($capsule) {
          return $capsule;
          };
         */

        /*
          $app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
          $app->add(new \App\Middleware\OldInputMiddleware($container));
          $app->add(new \App\Middleware\CsrfViewMiddleware($container));
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

 */



/*

        //错误控制接管
        $this->container->get('errorhandler')->register($error);

        $this->autoload();die;
        if ($param == 'getContainer') {
            return $this->container;
        } elseif (is_array($param) || $param === null) {
            $this->container->get('router')->route($param);
        } else {
            //$exception->showError('Internal Server Error');
        }

        //注册服务别名
        if (isset($general['services']) && is_array($general['services'])) {
            $container->setAlias($general['services']);
        }

        //错误处理
        $exception = $container->get('exception');
        set_error_handler(array($exception, 'errorHandler'), $error);    // 错误处理
        set_exception_handler(array($exception, 'exceptionHandler'));     // 异常处理
        register_shutdown_function(array($exception, 'shutdownHandler')); //PHP 执行错误

        if ($param == 'getUnframed') {
            return $container;
        } elseif (is_array($param)) {
            $container->get('router')->route($param);
        } else {
            $exception->showError('Internal Server Error');
        }
 * 
 */
    }

}