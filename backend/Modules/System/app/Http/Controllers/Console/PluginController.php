<?php

namespace Modules\System\Http\Controllers\Console;

use Illuminate\Http\Request;
use Modules\System\Models\Plugin;

class PluginController extends \Modules\System\Http\Controllers\BaseApiController
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $plugins = Plugin::latest()->get();

        return $this->success($plugins, 'Plugins retrieved successfully');
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:plugins,slug',
            'version' => 'nullable|string',
            'description' => 'nullable|string',
            'author' => 'nullable|string',
            'author_url' => 'nullable|url',
            'plugin_url' => 'nullable|url',
            'main_file' => 'nullable|string',
            'settings' => 'nullable|array',
            'priority' => 'integer|min:1|max:100',
        ]);

        $plugin = Plugin::create($validated);

        return $this->success($plugin, 'Plugin created successfully', 201);
    }

    public function show(Plugin $plugin): \Illuminate\Http\JsonResponse
    {
        return $this->success($plugin, 'Plugin retrieved successfully');
    }

    public function update(Request $request, Plugin $plugin): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:plugins,slug,'.$plugin->id,
            'version' => 'nullable|string',
            'description' => 'nullable|string',
            'author' => 'nullable|string',
            'author_url' => 'nullable|url',
            'plugin_url' => 'nullable|url',
            'main_file' => 'nullable|string',
            'settings' => 'nullable|array',
            'priority' => 'integer|min:1|max:100',
        ]);

        $plugin->update($validated);

        return $this->success($plugin, 'Plugin updated successfully');
    }

    public function destroy(Plugin $plugin): \Illuminate\Http\JsonResponse
    {
        if ($plugin->is_active) {
            return $this->validationError(['plugin' => ['Cannot delete active plugin. Deactivate it first.']], 'Cannot delete active plugin. Deactivate it first.');
        }

        $plugin->delete();

        return $this->success(null, 'Plugin deleted successfully');
    }

    public function activate(Plugin $plugin): \Illuminate\Http\JsonResponse
    {
        $plugin->activate();

        return $this->success([
            'plugin' => $plugin->fresh(),
        ], 'Plugin activated successfully');
    }

    public function deactivate(Plugin $plugin): \Illuminate\Http\JsonResponse
    {
        $plugin->deactivate();

        return $this->success([
            'plugin' => $plugin->fresh(),
        ], 'Plugin deactivated successfully');
    }

    public function updateSettings(Request $request, Plugin $plugin): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $plugin->update(['settings' => $validated['settings']]);

        return $this->success($plugin, 'Plugin settings updated successfully');
    }

    public function getActive(): \Illuminate\Http\JsonResponse
    {
        $plugins = Plugin::getActivePlugins();

        return $this->success($plugins, 'Active plugins retrieved successfully');
    }
}
