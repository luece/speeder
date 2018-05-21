<?php
namespace Unframed\Handlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Body;
use UnexpectedValueException;
use Monolog\Logger;
use Whoops\Handler\Handler;
use Dopesong\Slim\Error\Whoops;
use Psr\Container\ContainerInterface;

/**
 * 
 */
class Error extends AbstractHandler
{

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function get()
    {
        $logger = $this->container['logger']->get('unframed', Logger::WARNING);
        $whoopsHandler = new Whoops();

        // @var \Exception $exception 
        $whoopsHandler->pushHandler(function ($exception) use ($logger) {
            $logger->error($exception->getMessage(), ['exception' => $exception]);
            return Handler::DONE;
        });

        return $whoopsHandler;
    }

    /**
     * Invoke error handler
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     * @param \Exception             $exception The caught Exception object
     *
     * @return ResponseInterface
     * @throws UnexpectedValueException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, \Exception $exception)
    {
        $logger = $this->container['logger']->get('unframed', Logger::WARNING);
        if ($this->container->get('settings')['displayErrorDetails']) {

            $whoopsHandler = new Whoops();

            // @var \Exception $exception 
            $whoopsHandler->pushHandler(function ($exception) use ($logger) {
                $logger->error($exception->getMessage(), ['exception' => $exception]);
                return Handler::DONE;
            });

            return $whoopsHandler($request, $response, $exception);
        } else {
            $contentType = $this->determineContentType($request);

            switch ($contentType) {
                case 'application/json':
                    $output = $this->renderJsonErrorMessage($exception);
                    break;

                case 'text/xml':
                case 'application/xml':
                    $output = $this->renderXmlErrorMessage($exception);
                    break;

                case 'text/html':
                    $output = $this->renderHtmlErrorMessage($exception);
                    break;

                default:
                    throw new UnexpectedValueException('Cannot render unknown content type ' . $contentType);
            }

            $logger->error($exception->getMessage(), ['exception' => $exception]);

            $body = new Body(fopen('php://temp', 'r+'));
            $body->write($output);

            return $response
                            ->withStatus(500)
                            ->withHeader('Content-Type', $contentType)
                            ->withBody($body);
        }
    }

    /**
     * Render HTML error page
     *
     * @param  \Exception $exception
     *
     * @return string
     */
    protected function renderHtmlErrorMessage(\Exception $exception)
    {
        $title = 'Unframed Application Error';

        $html = 'A website error has occurred. Sorry for the temporary inconvenience.';

        $output = sprintf($this->html, $title, $title, $html, '', VERSION, RELEASE);

        return $output;
    }

    /**
     * Render exception as HTML.
     *
     * Provided for backwards compatibility; use renderHtmlExceptionOrError().
     *
     * @param \Exception $exception
     *
     * @return string
     */
    protected function renderHtmlException(\Exception $exception)
    {
        return $this->renderHtmlExceptionOrError($exception);
    }

    /**
     * Render exception or error as HTML.
     *
     * @param \Exception|\Error $exception
     *
     * @return string
     */
    protected function renderHtmlExceptionOrError($exception)
    {
        if (!$exception instanceof \Exception && !$exception instanceof \Error) {
            throw new \RuntimeException('Unexpected type. Expected Exception or Error.');
        }

        $html = sprintf('<div><strong>Type:</strong> %s</div>', get_class($exception));

        if (($code = $exception->getCode())) {
            $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        }

        if (($message = $exception->getMessage())) {
            $html .= sprintf('<div><strong>Message:</strong> %s</div>', htmlentities($message));
        }

        if (($file = $exception->getFile())) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
        }

        if (($line = $exception->getLine())) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
        }

        if (($trace = $exception->getTraceAsString())) {
            $html .= '<h2>Trace</h2>';
            $html .= sprintf('<pre>%s</pre>', htmlentities($trace));
        }

        return $html;
    }

    /**
     * Render JSON error
     *
     * @param \Exception $exception
     *
     * @return string
     */
    protected function renderJsonErrorMessage(\Exception $exception)
    {
        $error = [
            'message' => 'Unframed Application Error',
        ];

        return json_encode($error, JSON_PRETTY_PRINT);
    }

    /**
     * Render XML error
     *
     * @param \Exception $exception
     *
     * @return string
     */
    protected function renderXmlErrorMessage(\Exception $exception)
    {
        $xml = "<error>\n  <message>Unframed Application Error</message>\n";
        $xml .= '</error>';

        return $xml;
    }

    /**
     * Returns a CDATA section with the given content.
     *
     * @param  string $content
     * @return string
     */
    private function createCdataSection($content)
    {
        return sprintf('<![CDATA[%s]]>', str_replace(']]>', ']]]]><![CDATA[>', $content));
    }

}
