<?php

namespace Fuad\Reactor\Definitions;

class RelationDefinition
{
    public function __construct(
        public string $type,
        public string $relatedModel,
        public string $methodName,
        public ?string $foreignKey = null,
        public ?string $pivotTable = null
    ) {}

    public static function make(string $type, string $relatedModel, string $methodName): self
    {
        return new self($type, $relatedModel, $methodName);
    }

    public function foreignKey(?string $key): self
    {
        $this->foreignKey = $key;
        return $this;
    }

    // اجعل البارامتر يقبل Null أو String
    public function pivotTable(?string $table): self
    {
        if ($table !== null) {
            $this->pivotTable = $table;
        }
        return $this;
    }
}