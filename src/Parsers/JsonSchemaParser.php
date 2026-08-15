<?php

namespace Fuad\Reactor\Parsers;

use Fuad\Reactor\Definitions\TableDefinition;
use Fuad\Reactor\Definitions\ColumnDefinition;
use Illuminate\Support\Facades\File;

class JsonSchemaParser
{
    public static function parse(string $filePath): TableDefinition
    {
        $json = File::get($filePath);
        $data = json_decode($json, true);

        $schema = TableDefinition::model($data['model']);

        // 1. قراءة الأعمدة
        if (isset($data['columns']) && is_array($data['columns'])) {
            foreach ($data['columns'] as $col) {
                $column = ColumnDefinition::make($col['name'])
                    ->type($col['type'] ?? 'string');

                if (!empty($col['nullable'])) {
                    $column->nullable();
                }

                $schema->addColumn($column);
            }
        }

        // 2. قراءة العلاقات
        if (isset($data['relations']) && is_array($data['relations'])) {
            foreach ($data['relations'] as $rel) {
                $type = $rel['type'];
                $model = $rel['model'];

                if ($type === 'belongsTo') {
                    $schema->belongsTo($model, $rel['method'] ?? null, $rel['foreign_key'] ?? null);
                } elseif ($type === 'hasMany') {
                    $schema->hasMany($model, $rel['method'] ?? null);
                } elseif ($type === 'hasOne') {
                    $schema->hasOne($model, $rel['method'] ?? null);
                } elseif ($type === 'belongsToMany') {
                    $schema->belongsToMany($model, $rel['method'] ?? null, $rel['pivot_table'] ?? null);
                }
            }
        }

        return $schema;
    }
}