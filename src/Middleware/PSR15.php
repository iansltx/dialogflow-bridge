<?php

namespace iansltx\DialogflowBridge\Middleware;

use iansltx\DialogflowBridge\Router;
use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PSR15 implements MiddlewareInterface
{
    protected $router;
    protected $createJsonResponse;

    /**
     * @param Router $router
     * @param callable $createJsonResponse given some JSON-able data (an Answer, specifically),
     *   returns a new response with that data encoded to JSON as the response body, plus the
     *   proper Content-type header, e.g.
     *
     * function($data) { return new \Zend\Diactoros\JsonResponse($data); }
     */
    public function __construct(Router $router, callable $createJsonResponse)
    {
        $this->router = $router;
        $this->createJsonResponse = $createJsonResponse;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response. This conforms to the
     * draft PSR-15/http-interop middleware spec.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return ($this->createJsonResponse)($this->router->dispatch($request));
    }
}
