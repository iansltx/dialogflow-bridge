<?php

namespace iansltx\ApiAiBridge\Middleware;

use iansltx\ApiAiBridge\Router;
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
     * @param callable|null $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $response->getBody()->write(json_encode($this->router->dispatch($request)));
        return $response->withHeader('Content-type', 'application/json');
    }
}
