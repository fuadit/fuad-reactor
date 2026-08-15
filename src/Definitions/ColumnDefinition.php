<?php

namespace Fuad\Reactor\Definitions;

class ColumnDefinition
{
    public function __construct(
        public string $name,
        public string $type = 'string',
        public bool $nullable = false,
        public bool $fillable = true
    ) {}

    public static function make(string $name): self
    {
        return new self($name);
    }

    public function type(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function nullable(): self
    {
        $this->nullable = true;
        return $this;
    }
}