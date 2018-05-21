<?php
namespace Unframed\Handlers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Body;
use UnexpectedValueException;

/**
 * 
 */
class NotAllowed extends AbstractHandler
{

    /**
     * Invoke error handler
     *
     * @param  ServerRequestInterface $request  The most recent Request object
     * @param  ResponseInterface      $response The most recent Response object
     * @param  string[]               $methods  Allowed HTTP methods
     *
     * @return ResponseInterface
     * @throws UnexpectedValueException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $methods)
    {
        if ($request->getMethod() === 'OPTIONS') {
            $status = 200;
            $contentType = 'text/plain';
            $output = $this->renderPlainOptionsMessage($methods);
        } else {
            $status = 405;
            $contentType = $this->determineContentType($request);
            switch ($contentType) {
                case 'application/json':
                    $output = $this->renderJsonNotAllowedMessage($methods);
                    break;

                case 'text/xml':
                case 'application/xml':
                    $output = $this->renderXmlNotAllowedMessage($methods);
                    break;

                case 'text/html':
                    $output = $this->renderHtmlNotAllowedMessage($methods);
                    break;
                default:
                    throw new UnexpectedValueException('Cannot render unknown content type ' . $contentType);
            }
        }

        $body = new Body(fopen('php://temp', 'r+'));
        $body->write($output);
        $allow = implode(', ', $methods);

        return $response
                        ->withStatus($status)
                        ->withHeader('Content-type', $contentType)
                        ->withHeader('Allow', $allow)
                        ->withBody($body);
    }

    /**
     * Render PLAIN message for OPTIONS response
     *
     * @param  array                  $methods
     * @return string
     */
    protected function renderPlainOptionsMessage($methods)
    {
        $allow = implode(', ', $methods);

        return 'Allowed methods: ' . $allow;
    }

    /**
     * Render JSON not allowed message
     *
     * @param  array                  $methods
     * @return string
     */
    protected function renderJsonNotAllowedMessage($methods)
    {
        $allow = implode(', ', $methods);

        return '{"message":"Method not allowed. Must be one of: ' . $allow . '"}';
    }

    /**
     * Render XML not allowed message
     *
     * @param  array                  $methods
     * @return string
     */
    protected function renderXmlNotAllowedMessage($methods)
    {
        $allow = implode(', ', $methods);

        return "<root><message>Method not allowed. Must be one of: $allow</message></root>";
    }

    /**
     * Render HTML not allowed message
     *
     * @param  array                  $methods
     * @return string
     */
    protected function renderHtmlNotAllowedMessage($methods)
    {
        $allow = implode(', ', $methods);

        $title = 'Method not allowed';

        $html = 'Method not allowed. Must be one of: <strong>' . $allow . '</strong>';

        $output = sprintf($this->html, $title, $title, $html, '', VERSION, RELEASE);

        return $output;
    }

}
