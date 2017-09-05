<?php

namespace iansltx\ApiAiBridge\Container;

use Psr\Container\ContainerInterface;

class ClosureWrapper implements ContainerInterface
{
    protected $closures;

    public function __construct(array $closures)
    {
        $this->closures = array_filter($closures, function(\Closure $closure) { return true; }); // type check!
    }

    /** @inheritdoc */
    public function get($id)
    {
        if (isset($this->closures[$id])) {
            return $this->closures[$id];
        }
        throw new NoMatchedActionException("$id was not found in the action-to-closure mapping");
    }

    /** @inheritdoc */
    public function has($id)
    {
        return isset($this->closures[$id]);
    }
}
