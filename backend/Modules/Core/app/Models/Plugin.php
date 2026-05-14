<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $version
 * @property string|null $description
 * @property string|null $author
 * @property string|null $author_url
 * @property string|null $plugin_url
 * @property string|null $main_file
 * @property array<string, mixed>|null $settings
 * @property bool $is_active
 * @property int $priority
 */
class Plugin extends Model
{
    protected $table = 'core_plugins';


    protected $fillable = [
        'name',
        'slug',
        'version',
        'description',
        'author',
        'author_url',
        'plugin_url',
        'main_file',
        'settings',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function activate(): void
    {
        $this->update(['is_active' => true]);
        // Trigger plugin activation hook
        event(new \Modules\Core\Events\PluginActivated($this));
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
        // Trigger plugin deactivation hook
        event(new \Modules\Core\Events\PluginDeactivated($this));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, self>
     */
    public static function getActivePlugins(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('is_active', true)
            ->orderBy('priority')
            ->get();
    }
}
