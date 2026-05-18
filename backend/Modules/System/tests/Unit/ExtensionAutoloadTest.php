<?php

declare(strict_types=1);

namespace Modules\System\Tests\Unit;

use Extensions\TestPlugin\DummyClass;
use Illuminate\Support\Facades\File;
use Modules\System\Models\Extension;
use Modules\System\Providers\ExtensionAutoloadServiceProvider;
use Tests\TestCase;

class ExtensionAutoloadTest extends TestCase
{
    protected string $tempPluginPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Define temporary plugin path
        $this->tempPluginPath = base_path('Plugins/test-plugin');
    }

    protected function tearDown(): void
    {
        // Cleanup database
        Extension::where('slug', 'test-plugin')->forceDelete();

        // Cleanup temporary files and folders
        if (File::exists($this->tempPluginPath)) {
            File::deleteDirectory($this->tempPluginPath);
        }

        parent::tearDown();
    }

    /**
     * Test dynamic PSR-4 class loading for active extensions.
     */
    public function test_can_dynamically_autoload_active_extension_classes(): void
    {
        // 1. Create a dummy PHP class in the test-plugin folder
        $srcPath = $this->tempPluginPath.'/src';
        File::makeDirectory($srcPath, 0755, true, true);

        $classContent = <<<'PHP'
<?php

namespace Extensions\TestPlugin;

class DummyClass
{
    public function greet(): string
    {
        return 'Hello from PnP Engine';
    }
}
PHP;

        File::put($srcPath.'/DummyClass.php', $classContent);

        // 2. Create the active extension record in database
        Extension::create([
            'slug' => 'test-plugin',
            'type' => 'plugin',
            'name' => 'Test Plugin Autoload',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
            'author' => 'Test Author',
            'license' => 'MIT',
        ]);

        // 3. Instantiate and run the autoloader provider
        $provider = new ExtensionAutoloadServiceProvider(app());
        $provider->register();

        // 4. Verify class is auto-loaded and fully instantiable!
        $this->assertTrue(class_exists(DummyClass::class));

        $dummyInstance = new DummyClass;
        $this->assertEquals('Hello from PnP Engine', $dummyInstance->greet());
    }
}
