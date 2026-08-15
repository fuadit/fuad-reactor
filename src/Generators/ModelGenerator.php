<?php 

namespace Fuad\Reactor\Generators;

use Fuad\Reactor\Definitions\TableDefinition;
use Illuminate\Support\Facades\File;

class ModelGenerator
{
    public static function generate(TableDefinition $schema): void
    {
        $stub = File::get(__DIR__ . '/../../stubs/model.stub');

        $fillables = array_filter($schema->columns, fn($col) => $col->fillable);
        $fillableString = implode(",\n", array_map(fn($col) => "        '{$col->name}'", $fillables));

        $content = str_replace(
            ['{{modelName}}', '{{fillable}}'],
            [$schema->modelName, $fillableString],
            $stub
        );

        $path = app_path("Models/{$schema->modelName}.php");
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
    }
}