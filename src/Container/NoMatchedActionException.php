<?php

namespace iansltx\DialogflowBridge\Container;

use Psr\Container\NotFoundExceptionInterface;

class NoMatchedActionException extends \InvalidArgumentException implements NotFoundExceptionInterface
{
}
