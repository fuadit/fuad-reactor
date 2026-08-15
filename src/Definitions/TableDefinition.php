<?php 

namespace Fuad\Reactor\Definitions;

class TableDefinition
{
    public string $modelName;
    /** @var ColumnDefinition[] */
    public array $columns = [];

    public static function model(string $name): self
    {
        $instance = new self();
        $instance->modelName = $name;
        return $instance;
    }

    public function addColumn(ColumnDefinition $column): self
    {
        $this->columns[] = $column;
        return $this;
    }
}