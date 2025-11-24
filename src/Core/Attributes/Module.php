<?php

namespace NeoPhp\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Module
{
    public array $controllers = [];
    public array $providers = [];
    public array $imports = [];
    public array $exports = [];

    public function __construct(
        array $controllers = [],
        array $providers = [],
        array $imports = [],
        array $exports = []
    ) {
        $this->controllers = $controllers;
        $this->providers = $providers;
        $this->imports = $imports;
        $this->exports = $exports;
    }

    public function getControllers(): array
    {
        return $this->controllers;
    }

    public function getProviders(): array
    {
        return $this->providers;
    }

    public function getImports(): array
    {
        return $this->imports;
    }

    public function getExports(): array
    {
        return $this->exports;
    }
}
