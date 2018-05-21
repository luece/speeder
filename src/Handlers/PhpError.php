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
 * Default Unframed application error handler for PHP 7+ Throwables
 *
 * It outputs the error message and diagnostic information in either JSON, XML,
 * or HTML based on the Accept header.
 */
class PhpError extends AbstractHandler
{

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Invoke error handler
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     * @param \Throwable             $error     The caught Throwable object
     *
     * @return ResponseInterface
     * @throws UnexpectedValueException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, \Throwable $error)
    {
        $logger = $this->container['logger']->get('unframed', Logger::WARNING);
        if ($this->container->get('settings')['displayErrorDetails']) {

            $whoopsHandler = new Whoops();

            // @var \Exception $exception 
            $whoopsHandler->pushHandler(function ($error) use ($logger) {
                $logger->error($error->getMessage(), ['error' => $error]);
                return Handler::DONE;
            });

            return $whoopsHandler($request, $response, $error);
        } else {
            $contentType = $this->determineContentType($request);
            switch ($contentType) {
                case 'application/json':
                    $output = $this->renderJsonErrorMessage($error);
                    break;

                case 'text/xml':
                case 'application/xml':
                    $output = $this->renderXmlErrorMessage($error);
                    break;

                case 'text/html':
                    $output = $this->renderHtmlErrorMessage($error);
                    break;
                default:
                    throw new UnexpectedValueException('Cannot render unknown content type ' . $contentType);
            }

            $logger->error($error->getMessage(), ['error' => $error]);

            $body = new Body(fopen('php://temp', 'r+'));
            $body->write($output);

            return $response
                            ->withStatus(500)
                            ->withHeader('Content-type', $contentType)
                            ->withBody($body);
        }
    }

    /**
     * Render HTML error page
     *
     * @param \Throwable $error
     *
     * @return string
     */
    protected function renderHtmlErrorMessage(\Throwable $error)
    {
        $title = 'Unframed Application Error';


        $html = '<p>A website error has occurred. Sorry for the temporary inconvenience.</p>';

        $output = sprintf($this->html, $title, $title, $html, '', VERSION, RELEASE);

        return $output;
    }

    /**
     * Render error as HTML.
     *
     * @param \Throwable $error
     *
     * @return string
     */
    protected function renderHtmlError(\Throwable $error)
    {
        $html = sprintf('<div><strong>Type:</strong> %s</div>', get_class($error));

        if (($code = $error->getCode())) {
            $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        }

        if (($message = $error->getMessage())) {
            $html .= sprintf('<div><strong>Message:</strong> %s</div>', htmlentities($message));
        }

        if (($file = $error->getFile())) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
        }

        if (($line = $error->getLine())) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
        }

        if (($trace = $error->getTraceAsString())) {
            $html .= '<h2>Trace</h2>';
            $html .= sprintf('<pre>%s</pre>', htmlentities($trace));
        }

        return $html;
    }

    /**
     * Render JSON error
     *
     * @param \Throwable $error
     *
     * @return string
     */
    protected function renderJsonErrorMessage(\Throwable $error)
    {
        $json = ['message' => 'Unframed Application Error',];

        return json_encode($json, JSON_PRETTY_PRINT);
    }

    /**
     * Render XML error
     *
     * @param \Throwable $error
     *
     * @return string
     */
    protected function renderXmlErrorMessage(\Throwable $error)
    {
        $xml = "<error>\n  <message>Unframed Application Error</message>\n";

        $xml .= "</error>";

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
