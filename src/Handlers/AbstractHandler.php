<?php
namespace Unframed\Handlers;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Abstract Slim application handler
 */
abstract class AbstractHandler
{

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
    
    /**
     *
     * @var string
     */
    protected $html = '<!DOCTYPE html><head><title>%s</title><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1" />'
            . '<style type="text/css">::selection{background-color:#68b4ff;color:white}::moz-selection{background-color:#68b4ff;color:white}'
            . '::webkit-selection{background-color:#68b4ff;color:white}body{margin:20px;font:13px/20px normal Helvetica,Arial,sans-serif;color:#4f5155}'
            . 'a{color:#09f;background-color:transparent;font-weight:normal}'
            . 'h1{color:#444;background-color:transparent;border-bottom:1px solid #d0d0d0;font-size:19px;font-weight:normal;margin:0 0 14px 0;padding:14px 15px 10px 15px}'
            . 'code{font-family:Consolas,Monaco,Courier New,Courier,monospace;font-size:12px;background-color:#f9f9f9;border:1px solid #d0d0d0;color:#002166;display:block;margin:14px 0 14px 0;padding:12px 10px 12px 10px}'
            . '#container{margin:0 auto;border:1px solid #d0d0d0;-webkit-box-shadow:0 0 8px #d0d0d0}p{margin:12px 15px 12px 15px}'
            . '</style></head><body><div id="container"><h1>%s</h1><p>%s %s</p></div><div class="container" style="text-align:center;">'
            . '<p class="text-muted">Powered by <a href="//unframed.cc/" title="unframed">Unframed</a> v%s.%s </p></div></body></html>';

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
