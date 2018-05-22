<?php
namespace Unframed\Core;

use Slim\Csrf\Guard;
use Slim\Flash\Messages;
use Slim\Http\Request;
use Slim\Http\Response;
use Unframed\Handlers\Logger;
use Unframed\Handlers\Error;
use Unframed\Handlers\PhpError;
use Unframed\Handlers\NotFound;
use Unframed\Handlers\NotAllowed;
use Unframed\Handlers\Router;
use Slim\Views\Twig;
use Slim\Views\PhpRenderer;

/**
 * Service Provider.
 */
class ServicesProvider
{

    /**
     * Register services.
     *
     * @param Container $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register($container)
    {
        //log handler
        $container['logger'] = function($container) {
            return new Logger($container);
        };

        // error handler
        $container['errorHandler'] = function ($container) {
            return new Error($container);
        };

        $container['phpErrorHandler'] = function ($container) {
            return new PhpError($container);
        };
        
        // notFoundHandler
        $container['notFoundHandler'] = function ($c) {
            return new NotFound;
        };

        // notAllowedHandler
        $container['notAllowedHandler'] = function ($c) {
            return new NotAllowed;
        };

        // CSRF protection middleware
        $container['csrf'] = function($container) {
            return new Guard;
        };

        // flash message
        $container['flash'] = function($container) {
            return new Messages;
        };

        // view
        $container['view'] = function ($container) {
            $settings = $container->get('settings');
            $path = $settings['app']['views'];

            if ($settings['viewType'] == 'php') {
                return new PhpRenderer($path);
            } else {
                $view = new Twig($path, ['cache' => false,]);

                /*
                $view->addExtension(new \Slim\Views\TwigExtension(
                        $container->router, $container->request->getUri()
                ));

                $view->getEnvironment()->addGlobal('auth', [
                    'check' => $container->auth->check(),
                    'user'  => $container->auth->user()
                ]);

                $view->getEnvironment()->addGlobal('flash', $container->flash);
*/
                return $view;
            }
        };


        //router
        if (!isset($container['routerHandler'])) {
            $container['routerHandler'] = function ($container) {
                return new Router;
            };
        }

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
    }

}
