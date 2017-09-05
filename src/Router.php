<?php

namespace iansltx\ApiAiBridge;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class Router
{
    protected $container;
    protected $fallback;

    /**
     * @param ContainerInterface $container
     * @param array $actionMap a mapping of action names to container dependency names; note that this
     *   is a whitelist so arbitrary action names don't call unexpected dependencies, rather than
     *   falling back to default behavior. See HandlerInterface for expected dependency signatures.
     * @param callable $fallback if provided, this handler will override the default fallback handler
     *   when a dependency does not exist for a supplied action. See HandlerInterface for signature.
     * @return static
     */
    public static function build(ContainerInterface $container, array $actionMap, callable $fallback = null) : self
    {
        return new static(new ContainerWrapper($container, $actionMap), $fallback);
    }

    /**
     * Protected to ensure that you know what you're doing when you pass a container directly into
     * the constructor without a mapping step. Actions will be mapped directly against container
     * dependencies in this case, so there's no layer of indirection that will prevent random
     * methods from being invoked in your application...so use with care!
     *
     * @param ContainerInterface $container
     * @param callable|null $fallback
     */
    protected function __construct(ContainerInterface $container, callable $fallback = null)
    {
        $this->container = $container;
        $this->fallback = $fallback ?: function(Question $question, Answer $answer) : Answer {
            return $answer;
        };
    }

    /**
     * Given an incoming request, figure out which dependency to use based on action mapping,
     * call that dependency, and return the result of that call (an Answer). If no dependency
     * matches, use the fallback handler.
     *
     * @param ServerRequestInterface $request
     * @return Answer
     */
    public function dispatch(ServerRequestInterface $request) : Answer
    {
        $question = Question::fromRequest($request);
        $baseAnswer = $question->getBaseAnswer();

        try {
            return ($this->container->get($question->getAction()))($question, $baseAnswer);
        } catch (NoMatchedActionException $e) {
            return ($this->fallback)($question, $baseAnswer);
        }
    }
}
