<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $theme = \Modules\Cms\Models\Theme::where('slug', 'janari')->toSql();
    echo "SQL: " . $theme . PHP_EOL;
    
    $result = \Modules\Cms\Models\Theme::where('slug', 'janari')->first();
    echo "Result: " . ($result ? $result->name : 'null') . PHP_EOL;
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
