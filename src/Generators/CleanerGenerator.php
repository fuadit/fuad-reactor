<?php

namespace Fuad\Reactor\Generators;

use Fuad\Reactor\Definitions\TableDefinition;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CleanerGenerator
{
    public static function clean(TableDefinition $schema): void
    {
        $modelName = $schema->modelName;
        $tableName = Str::snake(Str::pluralStudly($modelName));

        // 1. حذف الـ Model
        $modelPath = app_path("Models/{$modelName}.php");
        if (File::exists($modelPath)) {
            File::delete($modelPath);
        }

        // 2. حذف الـ Controller
        $controllerPath = app_path("Http/Controllers/{$modelName}Controller.php");
        if (File::exists($controllerPath)) {
            File::delete($controllerPath);
        }

        // 3. حذف الـ Resource
        $resourcePath = app_path("Http/Resources/{$modelName}Resource.php");
        if (File::exists($resourcePath)) {
            File::delete($resourcePath);
        }

        // 4. حذف ملفات الـ Migrations المرتبطة بالجدول الرئيسي
        self::deleteMigrationsMatching("_create_{$tableName}_table.php");

        // 5. حذف ملفات Pivot Migrations المرتبطة بـ Many to Many إن وجدت
        foreach ($schema->relations as $rel) {
            if ($rel->type === 'belongsToMany') {
                $models = [$modelName, $rel->relatedModel];
                sort($models);
                $pivotTable = $rel->pivotTable ?? Str::snake($models[0]) . '_' . Str::snake($models[1]);
                self::deleteMigrationsMatching("_create_{$pivotTable}_table.php");
            }
        }
    }

    protected static function deleteMigrationsMatching(string $pattern): void
    {
        $migrationFiles = File::glob(database_path('migrations/*.php'));

        foreach ($migrationFiles as $file) {
            if (Str::endsWith($file, $pattern)) {
                File::delete($file);
            }
        }
    }
}