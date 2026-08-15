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
        
        // أضف الـ foreign keys الخاصة بـ belongsTo إلى قائمة $fillable تلقائياً
        foreach ($schema->relations as $rel) {
            if ($rel->type === 'belongsTo' && $rel->foreignKey) {
                $fillables[] = (object) ['name' => $rel->foreignKey];
            }
        }

        $fillableString = implode(",\n", array_map(fn($col) => "        '{$col->name}'", $fillables));

        // بناء كود العلاقات (Relations Code)
        $relationsCode = [];
        foreach ($schema->relations as $rel) {
            $relatedClass = "\\App\\Models\\{$rel->relatedModel}";
            
            if ($rel->type === 'belongsTo') {
                $relationsCode[] = <<<PHP
    public function {$rel->methodName}()
    {
        return \$this->belongsTo({$relatedClass}::class, '{$rel->foreignKey}');
    }
PHP;
            } elseif ($rel->type === 'belongsToMany') {
                $pivot = $rel->pivotTable ? ", '{$rel->pivotTable}'" : "";
                $relationsCode[] = <<<PHP
    public function {$rel->methodName}()
    {
        return \$this->belongsToMany({$relatedClass}::class{$pivot});
    }
PHP;
            } else {
                // hasOne & hasMany
                $relationsCode[] = <<<PHP
    public function {$rel->methodName}()
    {
        return \$this->{$rel->type}({$relatedClass}::class);
    }
PHP;
            }
        }

        $relationsString = implode("\n\n", $relationsCode);

        $content = str_replace(
            ['{{modelName}}', '{{fillable}}', '{{relations}}'],
            [$schema->modelName, $fillableString, $relationsString],
            $stub
        );

        $path = app_path("Models/{$schema->modelName}.php");
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
    }
}