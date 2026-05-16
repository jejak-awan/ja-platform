<?php

namespace Modules\School\Http\Controllers\Api\Logistics;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Logistics\InventoryCategory;
use Modules\School\Models\Logistics\InventoryItem;
use Modules\School\Models\Logistics\InventoryTransaction;
use Modules\School\Services\Logistics\LogisticsService;
use Modules\School\Http\Requests\Logistics\StoreInventoryItemRequest;
use Modules\School\Http\Requests\Logistics\AdjustStockRequest;

class InventoryController extends BaseController
{
    public function __construct(protected LogisticsService $service)
    {
    }

    public function categories(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', InventoryCategory::class);
        $categories = $this->service->getInventoryCategories();
        return $this->sendResponse($categories, 'Categories retrieved successfully.');
    }

    public function items(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', InventoryItem::class);
        /** @var array<string, mixed> $filters */
        $filters = $request->all();
        $items = $this->service->getInventoryItems($filters);
        return $this->sendResponse($items, 'Inventory items retrieved successfully.');
    }

    public function storeItem(StoreInventoryItemRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', InventoryItem::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $item = $this->service->createInventoryItem($validated);
        return $this->sendResponse($item, 'Inventory item created successfully.', 201);
    }

    public function transactions(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', InventoryTransaction::class);
        /** @var \Illuminate\Support\Collection<int, InventoryTransaction> $transactions */
        $transactions = InventoryTransaction::with(['item', 'student'])->latest()->limit(50)->get();
        return $this->sendResponse($transactions, 'Inventory transactions retrieved successfully.');
    }

    public function adjustStock(AdjustStockRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', InventoryTransaction::class);
        try {
            /** @var array<string, mixed> $validated */
            $validated = $request->validated();
            $transaction = $this->service->adjustInventoryStock($validated);
            return $this->sendResponse($transaction, 'Stock adjusted successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }
}
