<?php

namespace iansltx\ApiAiBridge\Container;

use Psr\Container\NotFoundExceptionInterface;

class NoMatchedActionException extends \InvalidArgumentException implements NotFoundExceptionInterface
{
}
