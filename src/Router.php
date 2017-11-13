<?php

namespace iansltx\DialogflowBridge;

use iansltx\DialogflowBridge\Container\ClosureWrapper;
use iansltx\DialogflowBridge\Container\NoMatchedActionException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use iansltx\DialogflowBridge\Container\ContainerWrapper;

class Router
{
    protected $container;
    protected $fallback;

    /**
     * @param ContainerInterface $container
     * @param array $actionMap a mapping of action names to container dependency names; note that this
     *   is a whitelist so arbitrary action names don't call unexpected dependencies, rather than
     *   falling back to default behavior. See HandlerInterface for expected dependency signatures.
     * @param callable|null $fallback if provided, this handler will override the default fallback handler
     *   when a dependency does not exist for a supplied action. See HandlerInterface for signature.
     * @return static
     * @see __construct()
     */
    public static function build(ContainerInterface $container, array $actionMap, callable $fallback = null) : self
    {
        return new static(new ContainerWrapper($container, $actionMap), $fallback);
    }

    /**
     * @param \Closure[] $handlers an array with action names for keys and closures as values;
     *   closure signatures should match HandlerInterface::__invoke(), but this is not enforced.
     * @param callable|null $fallback if provided, this handler will override the default fallback handler
     *   when a dependency does not exist for a supplied action. See HandlerInterface for signature.
     * @return static
     * @see __construct()
     */
    public static function buildFromClosureArray(array $handlers, callable $fallback = null) : self
    {
        return new static(new ClosureWrapper($handlers), $fallback);
    }

    /**
     * Protected to ensure that you know what you're doing when you pass a container directly into
     * the constructor without a mapping step. Actions will be mapped directly against container
     * dependencies in this case, so there's no layer of indirection that will prevent random
     * methods from being invoked in your application...so use with care!
     *
     * @param ContainerInterface $container
     * @param callable|null $fallback; if null, adds a fallback callback that returns
     *   "I'm not sure how to answer that." as both speech and text. You'll want to
     *   override this.
     */
    protected function __construct(ContainerInterface $container, callable $fallback = null)
    {
        $this->container = $container;
        $this->fallback = $fallback ?: function(Question $question, Answer $answer) : Answer {
            return $answer->withSpeechAndText("I'm not sure how to answer that.");
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
