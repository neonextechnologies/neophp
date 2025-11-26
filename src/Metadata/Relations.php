<?php

namespace NeoPhp\Metadata;

use Attribute;

/**
 * Relation Attributes
 * Define model relationships
 */

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class HasOne
{
    public function __construct(
        public string $model,
        public ?string $foreignKey = null,
        public ?string $localKey = 'id'
    ) {}
}

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class HasMany
{
    public function __construct(
        public string $model,
        public ?string $foreignKey = null,
        public ?string $localKey = 'id'
    ) {}
}

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class BelongsTo
{
    public function __construct(
        public string $model,
        public ?string $foreignKey = null,
        public ?string $ownerKey = 'id'
    ) {}
}

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class BelongsToMany
{
    public function __construct(
        public string $model,
        public ?string $pivotTable = null,
        public ?string $foreignKey = null,
        public ?string $relatedKey = null
    ) {}
}

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class MorphTo
{
    public function __construct(
        public ?string $name = null
    ) {}
}

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class MorphOne
{
    public function __construct(
        public string $model,
        public string $name
    ) {}
}

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class MorphMany
{
    public function __construct(
        public string $model,
        public string $name
    ) {}
}
