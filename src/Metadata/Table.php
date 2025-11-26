<?php

namespace NeoPhp\Metadata;

use Attribute;

/**
 * Table Attribute
 * Define table name for model
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    public function __construct(
        public string $name,
        public ?string $connection = null,
        public ?string $engine = null
    ) {}
}
