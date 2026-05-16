<?php

namespace Modules\Layout\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Layout\Models\Widget;
use Modules\System\Http\Controllers\BaseApiController;
use Modules\System\Contracts\LayoutRegistryInterface;

class WidgetController extends BaseApiController
{
    protected LayoutRegistryInterface $registry;

    public function __construct(LayoutRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Widget::query();

        if ($request->has('module_scope')) {
            $query->where('module_scope', $request->input('module_scope'));
        }

        if ($request->has('location')) {
            $query->where('location', $request->input('location'));
        }

        $widgets = $query->orderBy('sort_order')->get();

        return $this->success($widgets, 'Widgets retrieved successfully');
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'location' => 'nullable|string',
            'settings' => 'nullable|array',
            'module_scope' => 'nullable|string',
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
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string',
            'location' => 'nullable|string',
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

    public function reorder(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'widgets' => 'required|array',
            'widgets.*.id' => 'required|exists:lay_widgets,id',
            'widgets.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->input('widgets') as $widgetData) {
            Widget::where('id', $widgetData['id'])
                ->update(['sort_order' => $widgetData['sort_order']]);
        }

        return $this->success(null, 'Widgets reordered successfully');
    }

    public function locations(Request $request): \Illuminate\Http\JsonResponse
    {
        $scope = $request->input('module_scope', 'cms');
        $locations = $this->registry->getWidgetLocations($scope);

        $formatted = array_map(function ($loc) {
            return ['id' => $loc, 'name' => ucwords(str_replace(['-', '_'], ' ', $loc))];
        }, $locations);

        return $this->success($formatted, 'Widget locations retrieved successfully');
    }

    public function getByLocation(string $location, Request $request): \Illuminate\Http\JsonResponse
    {
        $scope = $request->input('module_scope', 'cms');
        
        $widgets = Widget::where('location', $location)
            ->where('module_scope', $scope)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return $this->success($widgets, 'Widgets retrieved successfully');
    }
}
