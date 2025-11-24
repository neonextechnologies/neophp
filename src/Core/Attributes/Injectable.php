<?php

namespace NeoPhp\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Injectable
{
    public function __construct(
        public ?string $scope = 'singleton'
    ) {
    }
}
