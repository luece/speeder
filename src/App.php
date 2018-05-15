<?php
namespace Unframed;

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
class App
{
    
    public function __construct()
    {

        session_start();

        $post = BASE_PATH . 'src/Post/Resources/';
        

        // 配置
        $settings = [
            'settings' => [
                'displayErrorDetails'    => true, // set to false in production
                'addContentLengthHeader' => false, // Allow the web server to send the content-length header
                // Monolog settings
                'logger' => [
                    'name'  => 'unframed-app',
                    'path'  => BASE_PATH . 'public/data/logs/app.log',
                    'level' => \Monolog\Logger::DEBUG,
                ],
            ],
        ];

        $app = new \Slim\App($settings);

        $container = $app->getContainer();

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

        // 日志配置 monolog
        $container['logger'] = function ($c) {
            $settings = $c->get('settings')['logger'];
            $logger = new \Monolog\Logger($settings['name']);
            $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
            $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
            return $logger;
        };

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


// Routes

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    var_dump($args);
});

        return $app;
    }
}