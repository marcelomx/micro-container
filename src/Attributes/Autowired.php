<?php

declare(strict_types=1);

namespace MicroContainer\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Autowired
{
    public function __construct(
        public ?string $service = null
    ) {
    }
}
