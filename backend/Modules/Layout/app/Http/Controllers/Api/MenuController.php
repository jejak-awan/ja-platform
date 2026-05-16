<?php

namespace Modules\Layout\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Modules\Layout\Models\Menu;
use Modules\Layout\Models\MenuItem;
use Modules\System\Http\Controllers\BaseApiController;
use Modules\System\Contracts\LayoutRegistryInterface;

class MenuController extends BaseApiController
{
    protected LayoutRegistryInterface $registry;

    public function __construct(LayoutRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Menu::withCount('items');

        if ($request->has('module_scope')) {
            $query->where('module_scope', $request->input('module_scope'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $perPage = $request->integer('per_page', 15);
        $menus = $query->latest()->paginate($perPage);

        return $this->success($menus, 'Menus retrieved successfully');
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $scope = $request->input('module_scope', 'cms');
        $allowedLocations = $this->registry->getMenuLocations($scope);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:lay_menus,slug',
            'location' => 'nullable|string',
            'module_scope' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        $menu = Menu::create($validated);

        return $this->success($menu, 'Menu created successfully', 201);
    }

    public function show(Menu $menu): \Illuminate\Http\JsonResponse
    {
        return $this->success($menu->load('parentItems.children'), 'Menu retrieved successfully');
    }

    public function update(Request $request, Menu $menu): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:lay_menus,slug,' . $menu->id,
            'location' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $menu->update($validated);

        if ($menu->location) {
            Cache::forget("menu_location_{$menu->location}");
        }

        return $this->success($menu, 'Menu updated successfully');
    }

    public function destroy(Menu $menu): \Illuminate\Http\JsonResponse
    {
        $menu->delete();
        return $this->success(null, 'Menu deleted successfully');
    }

    public function addItem(Request $request, Menu $menu): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string',
            'type' => 'required|string',
            'target_id' => 'nullable',
            'target_type' => 'nullable|string',
            'parent_id' => 'nullable|exists:lay_menu_items,id',
            'icon' => 'nullable|string',
            'css_class' => 'nullable|string',
            'sort_order' => 'integer',
            'open_in_new_tab' => 'boolean',
            'metadata' => 'nullable|array',
        ]);

        $item = $menu->items()->create($validated);

        if ($menu->location) {
            Cache::forget("menu_location_{$menu->location}");
        }

        return $this->success($item, 'Menu item added successfully', 201);
    }

    public function reorderItems(Request $request, Menu $menu): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:lay_menu_items,id',
            'items.*.sort_order' => 'required|integer',
            'items.*.parent_id' => 'nullable|exists:lay_menu_items,id',
        ]);

        DB::transaction(function () use ($request, $menu) {
            foreach ($request->input('items') as $itemData) {
                MenuItem::where('id', $itemData['id'])
                    ->where('menu_id', $menu->id)
                    ->update([
                        'sort_order' => $itemData['sort_order'],
                        'parent_id' => $itemData['parent_id'] ?? null,
                    ]);
            }
        });

        if ($menu->location) {
            Cache::forget("menu_location_{$menu->location}");
        }

        return $this->success(null, 'Menu items reordered successfully');
    }

    public function getByLocation(string $location): \Illuminate\Http\JsonResponse
    {
        $cacheKey = "menu_location_{$location}";

        $menu = Cache::remember($cacheKey, 3600, function () use ($location) {
            return Menu::where('location', $location)
                ->where('is_active', true)
                ->with(['parentItems.children'])
                ->first();
        });

        if (! $menu) {
            return $this->success(null, 'No active menu found for this location');
        }

        return $this->success($menu, 'Menu retrieved successfully');
    }
}
