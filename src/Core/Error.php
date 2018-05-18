<?php
namespace Unframed\Core;

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
class Error
{

    protected $container;

    /**
     * Known handled content types
     *
     * @var array
     */
    protected $knownContentTypes = [
        'application/json',
        'application/xml',
        'text/xml',
        'text/html',
    ];

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

        $output = sprintf(
                '<!DOCTYPE html><head><title>%s</title><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1" />'
                . '<style type="text/css">::selection{background-color:#68b4ff;color:white}::moz-selection{background-color:#68b4ff;color:white}'
                . '::webkit-selection{background-color:#68b4ff;color:white}body{margin:20px;font:13px/20px normal Helvetica,Arial,sans-serif;color:#4f5155}'
                . 'a{color:#09f;background-color:transparent;font-weight:normal}'
                . 'h1{color:#444;background-color:transparent;border-bottom:1px solid #d0d0d0;font-size:19px;font-weight:normal;margin:0 0 14px 0;padding:14px 15px 10px 15px}'
                . 'code{font-family:Consolas,Monaco,Courier New,Courier,monospace;font-size:12px;background-color:#f9f9f9;border:1px solid #d0d0d0;color:#002166;display:block;margin:14px 0 14px 0;padding:12px 10px 12px 10px}'
                . '#container{margin:0 auto;border:1px solid #d0d0d0;-webkit-box-shadow:0 0 8px #d0d0d0}p{margin:12px 15px 12px 15px}'
                . '</style></head><body><div id="container"><h1>%s</h1><p>%s</p></div><div class="container" style="text-align:center;">'
                . '<p class="text-muted">Powered by <a href="//unframed.cc/" title="unframed">Unframed</a> v%s.%s </p></div></body></html>', $title, $title, $html,VERSION,RELEASE
        );

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

    /**
     * Determine which content type we know about is wanted using Accept header
     *
     * Note: This method is a bare-bones implementation designed specifically for
     * Slim's error handling requirements. Consider a fully-feature solution such
     * as willdurand/negotiation for any other situation.
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function determineContentType(ServerRequestInterface $request)
    {
        $acceptHeader = $request->getHeaderLine('Accept');
        $selectedContentTypes = array_intersect(explode(',', $acceptHeader), $this->knownContentTypes);

        if (count($selectedContentTypes)) {
            return current($selectedContentTypes);
        }

        // handle +json and +xml specially
        if (preg_match('/\+(json|xml)/', $acceptHeader, $matches)) {
            $mediaType = 'application/' . $matches[1];
            if (in_array($mediaType, $this->knownContentTypes)) {
                return $mediaType;
            }
        }

        return 'text/html';
    }

}
