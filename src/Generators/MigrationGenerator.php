<?php

namespace Fuad\Reactor\Generators;

use Fuad\Reactor\Definitions\TableDefinition;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MigrationGenerator
{
    public static function generate(TableDefinition $schema): void
    {
        $tableName = Str::snake(Str::pluralStudly($schema->modelName));
        $timestamp = date('Y_m_d_His');
        $fileName = "{$timestamp}_create_{$tableName}_table.php";

        $columnsCode = [];
        foreach ($schema->columns as $col) {
            $line = "\$table->{$col->type}('{$col->name}')";
            if ($col->nullable) {
                $line .= "->nullable()";
            }
            $columnsCode[] = "            " . $line . ";";
        }

        // إضافة حقول العلاقات من نوع belongsTo تلقائياً
        foreach ($schema->relations as $rel) {
            if ($rel->type === 'belongsTo') {
                $relatedTable = Str::snake(Str::pluralStudly($rel->relatedModel));
                $columnsCode[] = "            \$table->foreignId('{$rel->foreignKey}')->constrained('{$relatedTable}')->onDelete('cascade');";
            }
        }

        $columnsString = implode("\n", $columnsCode);

        $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->id();
{$columnsString}
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};
PHP;

        $path = database_path("migrations/{$fileName}");
        File::put($path, $content);

        // توليد Pivot Table تلقائياً لعلاقات Many-to-Many
        foreach ($schema->relations as $rel) {
            if ($rel->type === 'belongsToMany') {
                self::generatePivotMigration($schema->modelName, $rel);
            }
        }
    }

    protected static function generatePivotMigration(string $modelName, $rel): void
    {
        $models = [$modelName, $rel->relatedModel];
        sort($models); // الترتيب الأبجدي الافتراضي للـ Pivot في لارافيل
        
        $pivotTable = $rel->pivotTable ?? Str::snake($models[0]) . '_' . Str::snake($models[1]);
        $timestamp = date('Y_m_d_His', time() + 1); // +1 لضمان تنفيذ المايجريشن بعد الجدول الأساسي
        
        $firstKey = Str::snake($models[0]) . '_id';
        $secondKey = Str::snake($models[1]) . '_id';
        
        $firstTable = Str::snake(Str::pluralStudly($models[0]));
        $secondTable = Str::snake(Str::pluralStudly($models[1]));

        $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$pivotTable}', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('{$firstKey}')->constrained('{$firstTable}')->onDelete('cascade');
            \$table->foreignId('{$secondKey}')->constrained('{$secondTable}')->onDelete('cascade');
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$pivotTable}');
    }
};
PHP;

        $fileName = "{$timestamp}_create_{$pivotTable}_table.php";
        File::put(database_path("migrations/{$fileName}"), $content);
    }
}