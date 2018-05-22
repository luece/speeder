<?php
namespace Unframed\Handlers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Body;
use UnexpectedValueException;

/**
 * 
 */
class NotFound extends AbstractHandler
{
    /**
     * Invoke not found handler
     *
     * @param  ServerRequestInterface $request  The most recent Request object
     * @param  ResponseInterface      $response The most recent Response object
     *
     * @return ResponseInterface
     * @throws UnexpectedValueException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        if ($request->getMethod() === 'OPTIONS') {
            $contentType = 'text/plain';
            $output = $this->renderPlainNotFoundOutput();
        } else {
            $contentType = $this->determineContentType($request);
            switch ($contentType) {
                case 'application/json':
                    $output = $this->renderJsonNotFoundOutput();
                    break;

                case 'text/xml':
                case 'application/xml':
                    $output = $this->renderXmlNotFoundOutput();
                    break;

                case 'text/html':
                    $output = $this->renderHtmlNotFoundOutput($request);
                    break;

                default:
                    throw new UnexpectedValueException('Cannot render unknown content type ' . $contentType);
            }
        }

        $body = new Body(fopen('php://temp', 'r+'));
        $body->write($output);

        return $response->withStatus(404)
                        ->withHeader('Content-Type', $contentType)
                        ->withBody($body);
    }

    /**
     * Render plain not found message
     *
     * @return ResponseInterface
     */
    protected function renderPlainNotFoundOutput()
    {
        return 'Not found';
    }

    /**
     * Return a response for application/json content not found
     *
     * @return ResponseInterface
     */
    protected function renderJsonNotFoundOutput()
    {
        return '{"message":"Not found"}';
    }

    /**
     * Return a response for xml content not found
     *
     * @return ResponseInterface
     */
    protected function renderXmlNotFoundOutput()
    {
        return '<root><message>Not found</message></root>';
    }

    /**
     * Return a response for text/html content not found
     *
     * @param  ServerRequestInterface $request  The most recent Request object
     *
     * @return ResponseInterface
     */
    protected function renderHtmlNotFoundOutput(ServerRequestInterface $request)
    {
        $homeUrl = (string) ($request->getUri()->withPath('')->withQuery('')->withFragment(''));

        $title = 'Page Not Found';

        $html = 'The page you are looking for could not be found. Check the address bar
            to ensure your URL is spelled correctly. If all else fails, you can
            visit our home page at the link below.';

        $output = sprintf($this->html, $title, $title, $html, '<a href="' . $homeUrl . '">Visit the Home Page</a>', VERSION, RELEASE);

        return $output;
    }
}
