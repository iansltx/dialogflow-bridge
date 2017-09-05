<?php

namespace iansltx\ApiAiBridge;

use Psr\Container\NotFoundExceptionInterface;

class NoMatchedActionException extends \InvalidArgumentException implements NotFoundExceptionInterface
{
}
