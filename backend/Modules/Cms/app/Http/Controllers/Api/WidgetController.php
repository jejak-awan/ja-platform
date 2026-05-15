<?php

namespace Modules\Cms\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Cms\Models\Widget;
use Modules\System\Http\Controllers\BaseApiController;

class WidgetController extends BaseApiController
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Widget::query();

        if ($request->has('location')) {
            $query->where('location', $request->input('location'));
        }

        $widgets = $query->orderBy('sort_order')->get();

        return $this->success($widgets, 'Widgets retrieved successfully');
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:text,html,recent_posts,categories,custom',
            'location' => 'nullable|string',
            'content' => 'nullable|string',
            'settings' => 'nullable|array',
            'sort_order' => 'integer',
        ]);

        $widget = Widget::create($validated);

        return $this->success($widget, 'Widget created successfully', 201);
    }

    public function show(Widget $widget): \Illuminate\Http\JsonResponse
    {
        return $this->success($widget, 'Widget retrieved successfully');
    }

    public function update(Request $request, Widget $widget): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:text,html,recent_posts,categories,custom',
            'location' => 'nullable|string',
            'content' => 'nullable|string',
            'settings' => 'nullable|array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $widget->update($validated);

        return $this->success($widget, 'Widget updated successfully');
    }

    public function destroy(Widget $widget): \Illuminate\Http\JsonResponse
    {
        $widget->delete();

        return $this->success(null, 'Widget deleted successfully');
    }

    public function getByLocation(Request $request, string $location): \Illuminate\Http\JsonResponse
    {
        $widgets = Widget::getByLocation($location);

        return $this->success($widgets, 'Widgets retrieved successfully');
    }

    public function reorder(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'widgets' => 'required|array',
            'widgets.*.id' => 'required|exists:widgets,id',
            'widgets.*.sort_order' => 'required|integer',
        ]);

        $widgets = $request->input('widgets');
        if (is_array($widgets)) {
            foreach ($widgets as $widgetData) {
                if (is_array($widgetData) && isset($widgetData['id']) && isset($widgetData['sort_order']) && is_numeric($widgetData['sort_order'])) {
                    Widget::where('id', $widgetData['id'])
                        ->update(['sort_order' => (int) $widgetData['sort_order']]);
                }
            }
        }

        return $this->success(null, 'Widgets reordered successfully');
    }

    public function locations(): \Illuminate\Http\JsonResponse
    {
        $defaultLocations = [
            ['id' => 'sidebar-1', 'name' => 'Main Sidebar'],
            ['id' => 'footer-1', 'name' => 'Footer Area 1'],
            ['id' => 'footer-2', 'name' => 'Footer Area 2'],
            ['id' => 'footer-3', 'name' => 'Footer Area 3'],
        ];

        /** @var \Illuminate\Support\Collection<int, array{id: string, name: string}> $dbLocations */
        $dbLocations = Widget::select('location')->distinct()->pluck('location')->filter()->map(function ($loc) {
            /** @var string $locStr */
            $locStr = $loc;

            return ['id' => $locStr, 'name' => ucwords(str_replace('-', ' ', $locStr))];
        });

        // Merge and unique by id
        $all = collect($defaultLocations)->concat($dbLocations)->unique('id')->values();

        return $this->success($all, 'Widget locations retrieved successfully');
    }
}
