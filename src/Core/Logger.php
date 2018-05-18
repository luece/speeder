<?php
namespace Unframed\Core;

use Monolog\Logger as log;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\FirePHPHandler;

/**
 * 
 * Logger::DEBUG (100): 详细的debug信息。
 * Logger::INFO (200): 关键事件。
 * Logger::NOTICE (250): 普通但是重要的事件。
 * Logger::WARNING (300): 出现非错误的异常。
 * Logger::ERROR (400): 运行时错误，但是不需要立刻处理。
 * Logger::CRITICA (500): 严重错误。
 * Logger::EMERGENCY (600): 系统不可用。
 */
class Logger
{

    public $container;

    /**
     * 
     * @param type $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * 
     * @param type $container
     */
    public static function set($container)
    {


        $settings['settings']['logger'] = [
            'name'  => 'Unframed',
            'path'  => $general['logPath'],
            'level' => log::DEBUG,
        ];

        // 日志配置 monolog
        $container['logger'] = $container->protect(function ($logName) use ($container) {
            
        });


        var_dump($this->container['app_id']);
        //var_dump($request);
        //var_dump($response);
        var_dump($args);
        //return $response;
    }

    /**
     * log
     * @param string $logName
     * @param int $logLevel
     * @return log
     */
    public function get($logName = 'unframed', $logLevel = log::DEBUG)
    {
        if (!isset($this->container[$logName])) {
            $this->container[$logName] = function ($container) use ($logName, $logLevel) {
                $logger = new log($logName);
                $logger->pushProcessor(new UidProcessor());
                $logger->pushProcessor(new WebProcessor());
                $logger->pushProcessor(new IntrospectionProcessor());
                $logger->pushProcessor(new MemoryUsageProcessor());
                $logger->pushProcessor(new MemoryPeakUsageProcessor());

                $logFile = $container->get('settings')['logger']['path'] . date('md') . '-' . $logName . '.log';
                $streamHandler = new StreamHandler($logFile, $logLevel);

                // the default date format is "Y-m-d H:i:s"
                $dateFormat = "Y-m-d H:i:s";

                // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
                $output = "[%datetime%] [%level_name%]  %message%\n%context%\n%extra%\n\n";

                $streamHandler->setFormatter(new LineFormatter($output, $dateFormat));
                $bufferHandler = new BufferHandler($streamHandler, 0, $logLevel, true, true);

                $logger->pushHandler($bufferHandler);
                $logger->pushHandler(new FirePHPHandler());
                return $logger;
            };
        }
        return $this->container[$logName];
    }

}
