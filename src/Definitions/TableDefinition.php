<?php 

namespace Fuad\Reactor\Definitions;

class TableDefinition
{
    public string $modelName;
    /** @var ColumnDefinition[] */
    public array $columns = [];
    /** @var RelationDefinition[] */
    public array $relations = [];

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

    // --- ميزات العلاقات الجديدة ---

    public function hasOne(string $relatedModel, ?string $methodName = null): self
    {
        $method = $methodName ?? lcfirst($relatedModel);
        $this->relations[] = RelationDefinition::make('hasOne', $relatedModel, $method);
        return $this;
    }

    public function belongsTo(string $relatedModel, ?string $methodName = null, ?string $foreignKey = null): self
    {
        $method = $methodName ?? lcfirst($relatedModel);
        $this->relations[] = RelationDefinition::make('belongsTo', $relatedModel, $method)
            ->foreignKey($foreignKey ?? lcfirst($relatedModel) . '_id');
        return $this;
    }

    public function hasMany(string $relatedModel, ?string $methodName = null): self
    {
        $method = $methodName ?? \Illuminate\Support\Str::plural(lcfirst($relatedModel));
        $this->relations[] = RelationDefinition::make('hasMany', $relatedModel, $method);
        return $this;
    }

    public function belongsToMany(string $relatedModel, ?string $methodName = null, ?string $pivotTable = null): self
    {
        $method = $methodName ?? \Illuminate\Support\Str::plural(lcfirst($relatedModel));
        $this->relations[] = RelationDefinition::make('belongsToMany', $relatedModel, $method)
            ->pivotTable($pivotTable);
        return $this;
    }
}