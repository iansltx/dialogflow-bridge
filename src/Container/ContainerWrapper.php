<?php

namespace iansltx\ApiAiBridge\Container;

use Psr\Container\ContainerInterface;

class ContainerWrapper implements ContainerInterface
{
    protected $container;
    protected $actionMap;

    public function __construct(ContainerInterface $container, array $actionMap)
    {
        $this->container = $container;
        $this->actionMap = $actionMap;
    }

    /** @inheritdoc */
    public function get($id)
    {
        if (isset($this->actionMap[$id])) {
            return $this->container->get($id);
        }
        throw new NoMatchedActionException("$id was not found in the action-to-dependency mapping");
    }

    /** @inheritdoc */
    public function has($id)
    {
        return isset($this->actionMap[$id]) && $this->container->has($this->actionMap[$id]);
    }
}
