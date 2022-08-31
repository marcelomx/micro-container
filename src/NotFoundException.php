<?php

declare(strict_types=1);

namespace MicroContainer;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \Exception implements NotFoundExceptionInterface
{
}
