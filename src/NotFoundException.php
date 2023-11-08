<?php

declare(strict_types=1);

namespace MicroContainer;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \Exception implements NotFoundExceptionInterface
{
    public static function forEntry(string $id, ?\Throwable $previous = null): static
    {
        if ($previous instanceof self) {
            return $previous;
        }

        return new static(
            "No entry was found for '{$id}' identifier",
            previous: $previous
        );
    }
}
