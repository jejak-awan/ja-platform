<?php

namespace Modules\Cms\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Cms\Models\Menu;
use Modules\Cms\Models\MenuItem;
use Modules\Cms\Models\Theme;
use Modules\System\Http\Controllers\BaseApiController;

class MenuController extends BaseApiController
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['getByLocation']);
        $this->middleware('permission:manage menus')->except(['getByLocation']);
    }
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Menu::withCount('allItems');

        if ($request->has('search')) {
            $searchRaw = $request->input('search');
            $search = is_string($searchRaw) ? $searchRaw : '';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%".(string)$search."%")
                    ->orWhere('slug', 'like', "%".(string)$search."%");
            });
        }

        if ($request->has('location')) {
            $locationRaw = $request->location;
            $location = is_string($locationRaw) ? $locationRaw : '';
            $query->where('location', $location);
        }

        // Soft deletes filter
        if ($request->has('trashed')) {
            $trashedRaw = $request->trashed;
            $trashed = is_string($trashedRaw) ? $trashedRaw : '';
            if ($trashed === 'only') {
                $query->onlyTrashed();
            } elseif ($trashed === 'with') {
                $query->withTrashed();
            }
        }

        $perPage = $request->integer('per_page', 10);
        $menus = $query->latest()->paginate($perPage);
        $trashedCount = Menu::onlyTrashed()->count();

        return response()->json([
            'success' => true,
            'message' => 'Menus retrieved successfully',
            'data' => $menus,
            'meta' => [
                'trashed_count' => $trashedCount,
            ],
        ]);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $allowedLocations = $this->allowedMenuLocations();

        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'slug' => [
                    'nullable',
                    'string',
                    \Illuminate\Validation\Rule::unique('menus')->whereNull('deleted_at'),
                ],
                'location' => ['nullable', 'string', Rule::in($allowedLocations)],
                'description' => 'nullable|string',
            ],
            [
                'location.in' => 'Invalid menu location. Allowed locations: '.implode(', ', $allowedLocations).'.',
            ]
        );

        $name = is_string($validated['name']) ? $validated['name'] : '';

        // Auto-generate slug from name if not provided
        if (empty($validated['slug'])) {
            $baseSlug = \Illuminate\Support\Str::slug($name);
            $slug = $baseSlug;
            $counter = 1;
            while (Menu::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$counter++;
            }
            $validated['slug'] = $slug;
        }

        $menu = Menu::create($validated);

        return $this->success($menu->load('items'), 'Menu created successfully', 201);
    }

    /**
     * @param  int  $id
     */
    public function show($id): \Illuminate\Http\JsonResponse
    {
        /** @var Menu $menu */
        $menu = Menu::withTrashed()->findOrFail($id);

        return $this->success($menu->load(['items.children']), 'Menu retrieved successfully');
    }

    public function update(Request $request, Menu $menu): \Illuminate\Http\JsonResponse
    {
        $allowedLocations = $this->allowedMenuLocations();

        $validated = $request->validate(
            [
                'name' => 'sometimes|required|string|max:255',
                'slug' => [
                    'sometimes',
                    'required',
                    'string',
                    \Illuminate\Validation\Rule::unique('menus')->ignore($menu->id)->whereNull('deleted_at'),
                ],
                'location' => ['nullable', 'string', Rule::in($allowedLocations)],
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ],
            [
                'location.in' => 'Invalid menu location. Allowed locations: '.implode(', ', $allowedLocations).'.',
            ]
        );

        $oldLocation = $menu->location;
        $menu->update($validated);

        // Clear cache for both old and new locations
        if ($oldLocation) {
            \Illuminate\Support\Facades\Cache::forget("menu_location_".(string) $oldLocation);
        }
        if ($menu->location && $menu->location !== $oldLocation) {
            \Illuminate\Support\Facades\Cache::forget("menu_location_".(string) $menu->location);
        }

        return $this->success($menu->load('items'), 'Menu updated successfully');
    }

    public function destroy(Menu $menu): \Illuminate\Http\JsonResponse
    {
        $menu->delete();

        return $this->success(null, 'Menu deleted successfully');
    }

    public function restore(int $id): \Illuminate\Http\JsonResponse
    {
        /** @var Menu $menu */
        $menu = Menu::withTrashed()->findOrFail($id);
        $menu->restore();

        return $this->success(null, 'Menu restored successfully');
    }

    /**
     * @param  int  $id
     */
    public function forceDelete($id): \Illuminate\Http\JsonResponse
    {
        /** @var Menu $menu */
        $menu = Menu::withTrashed()->findOrFail($id);

        // Clear cache
        if ($menu->location) {
            \Illuminate\Support\Facades\Cache::forget("menu_location_".(string) $menu->location);
        }

        $menu->forceDelete();

        return $this->success(null, 'Menu permanently deleted');
    }

    public function bulkAction(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'menu_ids' => 'required|array',
            'menu_ids.*' => 'exists:menus,id',
            'action' => 'required|in:delete,restore,force_delete',
        ]);

        $idsRaw = $request->menu_ids;
        $ids = is_array($idsRaw) ? $idsRaw : [];
        $actionRaw = $request->action;
        $action = is_string($actionRaw) ? $actionRaw : '';

        try {
            if ($action === 'delete') {
                $menus = Menu::whereIn('id', $ids)->get();
                foreach ($menus as $menu) {
                    if ($menu->location) {
                        \Illuminate\Support\Facades\Cache::forget("menu_location_".(string) $menu->location);
                    }
                    $menu->delete();
                }

                return $this->success(null, 'Selected menus deleted successfully');
            } elseif ($action === 'restore') {
                $menus = Menu::withTrashed()->whereIn('id', $ids)->get();
                foreach ($menus as $menu) {
                    if ($menu->location) {
                        \Illuminate\Support\Facades\Cache::forget("menu_location_".(string) $menu->location);
                    }
                    $menu->restore();
                }

                return $this->success(null, 'Selected menus restored successfully');
            } elseif ($action === 'force_delete') {
                $menus = Menu::withTrashed()->whereIn('id', $ids)->get();
                foreach ($menus as $menu) {
                    /** @var Menu $menu */
                    if ($menu->location) {
                        \Illuminate\Support\Facades\Cache::forget("menu_location_".(string) $menu->location);
                    }
                    $menu->items()->forceDelete();
                    $menu->forceDelete();
                }

                return $this->success(null, 'Selected menus permanently deleted');
            }
        } catch (\Exception $e) {
            return $this->error('Bulk action failed: '.$e->getMessage(), 500);
        }

        return $this->error('Invalid action', 422);
    }

    public function addItem(Request $request, Menu $menu): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string',
            'type' => 'required|in:link,page,post,category,custom,column_group',
            'target_id' => 'nullable|integer',
            'target_type' => 'nullable|string',
            'parent_id' => 'nullable|exists:menu_items,id',
            'icon' => 'nullable|string',
            'css_class' => 'nullable|string',
            'description' => 'nullable|string',
            'badge' => 'nullable|string',
            'badge_color' => 'nullable|string',
            'image' => 'nullable|string',
            'image_size' => 'nullable|string',
            'mega_menu_layout' => 'nullable|string',
            'mega_menu_column' => 'nullable|integer',
            'mega_menu_show_dividers' => 'boolean',
            'hide_label' => 'boolean',
            'heading' => 'nullable|string',
            'show_heading_line' => 'boolean',
            'sort_order' => 'integer',
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $targetTypeRaw = $validated['target_type'] ?? null;
        $targetType = is_string($targetTypeRaw) ? $targetTypeRaw : null;
        $typeRaw = $validated['type'] ?? null;
        $type = is_string($typeRaw) ? $typeRaw : '';

        // Security: Validate target_type against whitelist
        if ($targetType && ! in_array($targetType, MenuItem::ALLOWED_TARGET_TYPES)) {
            return $this->error('Invalid target type', 422);
        }

        // Auto-assign target_type based on type if not provided
        if (! $targetType && $type) {
            if (in_array($type, ['page', 'post'])) {
                $validated['target_type'] = 'Modules\Cms\Models\Content';
            } elseif ($type === 'category') {
                $validated['target_type'] = 'Modules\Cms\Models\Category';
            }
        }

        $item = $menu->items()->create($validated);

        // Clear menu cache
        if ($menu->location) {
            \Illuminate\Support\Facades\Cache::forget("menu_location_".(string) $menu->location);
        }

        return $this->success($item->load('children'), 'Menu item created successfully', 201);
    }

    /**
     * @param  string|int  $id
     * @param  int  $id
     */
    public function items($id): \Illuminate\Http\JsonResponse
    {
        /** @var Menu $menu */
        $menu = Menu::withTrashed()->findOrFail($id);

        /** @var mixed $allItems */
        $allItems = $menu->allItems;

        // Return all items flattened, frontend will build the tree
        return $this->success($allItems, 'Menu items retrieved successfully');
    }

    public function updateItem(Request $request, Menu $menu, MenuItem $menuItem): \Illuminate\Http\JsonResponse
    {
        if ($menuItem->menu_id !== $menu->id) {
            return $this->validationError(['menu_item' => ['Menu item does not belong to this menu']], 'Menu item does not belong to this menu');
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'url' => 'nullable|string',
            'type' => 'sometimes|required|in:link,page,post,category,custom,column_group',
            'target_id' => 'nullable|integer',
            'target_type' => 'nullable|string',
            'parent_id' => 'nullable|exists:menu_items,id',
            'icon' => 'nullable|string',
            'css_class' => 'nullable|string',
            'description' => 'nullable|string',
            'badge' => 'nullable|string',
            'badge_color' => 'nullable|string',
            'image' => 'nullable|string',
            'image_size' => 'nullable|string',
            'mega_menu_layout' => 'nullable|string',
            'mega_menu_column' => 'nullable|integer',
            'mega_menu_show_dividers' => 'boolean',
            'hide_label' => 'boolean',
            'heading' => 'nullable|string',
            'show_heading_line' => 'boolean',
            'sort_order' => 'integer',
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $targetTypeRaw = $validated['target_type'] ?? $menuItem->target_type;
        $targetType = is_string($targetTypeRaw) ? $targetTypeRaw : null;
        $typeRaw = $validated['type'] ?? $menuItem->type;
        $type = is_string($typeRaw) ? $typeRaw : '';

        // Security: Validate target_type against whitelist
        if ($targetType && ! in_array($targetType, MenuItem::ALLOWED_TARGET_TYPES)) {
            return $this->error('Invalid target type', 422);
        }

        // Auto-assign target_type based on type if not provided
        if (! $targetType && $type) {
            if (in_array($type, ['page', 'post'])) {
                $validated['target_type'] = 'Modules\Cms\Models\Content';
            } elseif ($type === 'category') {
                $validated['target_type'] = 'Modules\Cms\Models\Category';
            }
        }

        $menuItem->update($validated);

        // Clear menu cache
        if ($menu->location) {
            \Illuminate\Support\Facades\Cache::forget("menu_location_".(string) $menu->location);
        }

        return $this->success($menuItem->load('children'), 'Menu item updated successfully');
    }

    public function deleteItem(Menu $menu, MenuItem $menuItem): \Illuminate\Http\JsonResponse
    {
        if ($menuItem->menu_id !== $menu->id) {
            return $this->validationError(['menu_item' => ['Menu item does not belong to this menu']], 'Menu item does not belong to this menu');
        }

        $menuItem->delete();

        // Clear menu cache
        if ($menu->location) {
            \Illuminate\Support\Facades\Cache::forget("menu_location_".(string) $menu->location);
        }

        return $this->success(null, 'Menu item deleted successfully');
    }

    public function reorderItems(Request $request, Menu $menu): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.sort_order' => 'required|integer',
            'items.*.parent_id' => 'nullable|exists:menu_items,id',
        ]);

        $itemsRaw = $request->input('items', []);
        $items = is_array($itemsRaw) ? $itemsRaw : [];

        /** @var array<int, array<string, mixed>> $items */
        DB::transaction(function () use ($items, $menu) {
            foreach ($items as $itemData) {
                // $itemData is array<string, mixed> according to PHPDoc of $items

                $idRaw = $itemData['id'] ?? 0;
                $id = is_numeric($idRaw) ? (int) $idRaw : 0;
                $sortOrderRaw = $itemData['sort_order'] ?? 0;
                $sortOrder = is_numeric($sortOrderRaw) ? (int) $sortOrderRaw : 0;
                $parentIdRaw = $itemData['parent_id'] ?? null;
                $parentId = is_numeric($parentIdRaw) ? (int) $parentIdRaw : null;

                $updateData = [
                    'sort_order' => $sortOrder,
                    'parent_id' => $parentId,
                ];

                // Include optional mega menu fields if present
                $optionalFields = [
                    'title', 'url', 'icon', 'css_class', 'description',
                    'badge', 'badge_color', 'open_in_new_tab', 'image', 'image_size',
                    'mega_menu_layout', 'mega_menu_column', 'mega_menu_show_dividers',
                    'hide_label', 'heading', 'show_heading_line',
                ];
                foreach ($optionalFields as $field) {
                    if (array_key_exists($field, $itemData)) {
                        $updateData[$field] = $itemData[$field];
                    }
                }

                MenuItem::where('id', $id)
                    ->where('menu_id', $menu->id)
                    ->update($updateData);
            }
        });

        // Clear menu cache
        if ($menu->location) {
            \Illuminate\Support\Facades\Cache::forget("menu_location_".(string) $menu->location);
        }

        return $this->success(null, 'Menu items reordered successfully');
    }

    public function getByLocation(Request $request, string $location): \Illuminate\Http\JsonResponse
    {
        $cacheKey = "menu_location_{$location}";

        $menu = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($location) {
            $menu = Menu::getByLocation($location);

            if ($menu) {
                // Eager load items and their recursive children with their targets to fix N+1
                $menu->load([
                    'items' => function ($query) {
                        $query->with(['target', 'children' => function ($q) {
                            $q->with(['target', 'children' => function ($sq) {
                                $sq->with(['target']);
                            }]);
                        }]);
                    },
                ]);
            }

            return $menu;
        });

        if (! $menu) {
            return $this->success(null, 'No menu assigned to this location');
        }

        return $this->success($menu, 'Menu retrieved successfully');
    }

    /**
     * Resolve canonical menu locations from active frontend theme.
     *
     * @return array<int, string>
     */
    private function allowedMenuLocations(): array
    {
        /** @var Theme|null $activeTheme */
        $activeTheme = Theme::where('type', 'frontend')->where('is_active', true)->first();
        $manifest = $activeTheme?->getManifest();

        $locations = [];
        if (is_array($manifest) && isset($manifest['menus']) && is_array($manifest['menus'])) {
            $locations = array_values(array_filter(array_keys($manifest['menus']), static function ($key) {
                return is_string($key) && $key !== '';
            }));
        }

        if (empty($locations)) {
            $locations = ['header', 'header_top', 'footer', 'footer_col_1', 'footer_col_2', 'sidebar'];
        }

        return $locations;
    }
}
