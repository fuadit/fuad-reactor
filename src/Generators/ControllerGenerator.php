<?php 

namespace Fuad\Reactor\Generators;

use Fuad\Reactor\Definitions\TableDefinition;
use Illuminate\Support\Facades\File;

class ControllerGenerator
{
    public static function generate(TableDefinition $schema): void
    {
        $stub = File::get(__DIR__ . '/../../stubs/controller.stub');

        $content = str_replace(
            ['{{modelName}}', '{{modelNameLower}}'],
            [$schema->modelName, lcfirst($schema->modelName)],
            $stub
        );

        $path = app_path("Http/Controllers/{$schema->modelName}Controller.php");
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
    }
}