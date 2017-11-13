<?php

namespace iansltx\DialogflowBridge\Middleware;

use iansltx\DialogflowBridge\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DoublePass
{
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Double-pass middleware implementation
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param mixed $unused not hinted so can be used as either as a middleware or as a Slim 3 action
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $unused = null)
    {
        $response->getBody()->write(json_encode($this->router->dispatch($request)));
        return $response->withHeader('Content-type', 'application/json');
    }
}
