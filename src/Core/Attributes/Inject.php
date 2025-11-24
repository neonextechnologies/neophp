<?php

namespace NeoPhp\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Inject
{
    public function __construct(
        public string $token
    ) {
    }
}
