<?php 

namespace Fuad\Reactor\Generators;

use Fuad\Reactor\Definitions\TableDefinition;
use Illuminate\Support\Facades\File;

class ResourceGenerator
{
    public static function generate(TableDefinition $schema): void
    {
        $stub = File::get(__DIR__ . '/../../stubs/resource.stub');

        $attributes = array_map(
            fn($col) => "            '{$col->name}' => \$this->{$col->name}",
            $schema->columns
        );
        array_unshift($attributes, "            'id' => \$this->id");
        
        $attributesString = implode(",\n", $attributes);

        $content = str_replace(
            ['{{modelName}}', '{{attributes}}'],
            [$schema->modelName, $attributesString],
            $stub
        );

        $path = app_path("Http/Resources/{$schema->modelName}Resource.php");
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
    }
}