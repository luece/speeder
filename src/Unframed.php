<?php

use Slim\App;
use Unframed\Core\Logger;
use Unframed\Core\Error;

use Respect\Validation\Validator as v;
// Register routes
use Slim\Http\Request;
use Slim\Http\Response;

use Monolog\Logger as log;
use Whoops\Handler\Handler;
use Dopesong\Slim\Error\Whoops;
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
    public function run($param = array(), $displayError = false, $configPath = 'config/general_dev.php') 
    {
        session_start();
        require_once __DIR__ .'/../config/app.php';
        
        //set timezone
        date_default_timezone_set(TIME_ZONE);

        //read setting
        $settings = include SYS_PATH . 'config/settings.php';
        $settings['cli'] = PHP_SAPI == 'cli' ? true : false;
        $settings['logPath'] = BASE_PATH . $settings['logPath'] . date('Ym') . '/';
        $settings['displayErrorDetails'] = $displayError;
        
        $post = BASE_PATH . 'src/Post/Resources/';

        // error handler
        if ( $displayError ) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL | E_STRICT);
        } else {
            ini_set('display_errors', 0);
            error_reporting(0);
        }
        ini_set('log_errors', 1);
        ini_set('error_log', $settings['logPath'] . date('md') . '-php.log');

        // instantiation an Slim object
        $app = new App(['settings' => $settings]);

        // get container
        $container = $app->getContainer();

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

        // 中间件
        $container['csrf'] = function($container) {
            return new \Slim\Csrf\Guard;
        };

        /*

    $app->get('/{news}[/{year}[/{month}]]', function ($request, $response, $args) {
        
        var_dump($args);
       throw new \Exception('fdsa');
       
        
    })->setName('user-password-reset');
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
        $app->add($container->csrf);

        //v::with('App\\Validation\\Rules\\');
        //require __DIR__ . '/../app/routes.php';

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

        $app->run();
        return $app;


/*




        // 加载全局配置
        $this->loadGeneral();

        $this->container->setParameter('ERROR_MODE', $error);


        //ini_set('memory_limit','100M');
        ini_set('error_log', $this->container->getParameter('LOG_PATH') . date('Ym') . '/php-' . date('Y-m-d') . '.php');

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



        $unframed = new Unframed();


        //载入容器
        $container = new Unframed\DI\Container();

        // 载入全局配置
        $general = include SYS_PATH . $configPath;
        $container->setParameterAll($general);
        $container->setParameter('ERROR_MODE', $error);
        $container->setParameter('LOG_PATH', BASE_PATH . $container->getParameter('LOG_PATH'));
        $container->setParameter('DATA_PATH', BASE_PATH . $container->getParameter('DATA_PATH'));
        $container->setParameter('CONFIG_PATH', BASE_PATH . $container->getParameter('CONFIG_PATH'));

        //注册服务别名
        if (isset($general['services']) && is_array($general['services'])) {
            $container->setAlias($general['services']);
        }
        $container->set('Unframed', $unframed);
        $container->set('Unframed\DI\Container', $container);

        (PHP_SAPI == 'cli') && $container->setParameter('CLI_MODE', true);

        // 关闭错误显示和开启错误日志文件记录
        if ($error) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL | E_STRICT);
        } else {
            ini_set('display_errors', 0);
            error_reporting(0);
        }
        ini_set('log_errors', 1);
        ini_set('error_log', $container->getParameter('LOG_PATH') . 'log-php-' . date('Y-m-d') . '.php');

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
    
    public static function test($a, $b = '111') {
        echo $a . $b;
    }

}


