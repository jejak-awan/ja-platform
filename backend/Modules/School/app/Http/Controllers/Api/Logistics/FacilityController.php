<?php

namespace Modules\School\Http\Controllers\Api\Logistics;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Logistics\LandAsset;
use Modules\School\Models\Logistics\Building;
use Modules\School\Models\Logistics\Room;
use Modules\School\Models\Logistics\SchoolAsset;
use Modules\School\Services\Logistics\LogisticsService;
use Modules\School\Http\Requests\Logistics\StoreLandRequest;
use Modules\School\Http\Requests\Logistics\StoreBuildingRequest;
use Modules\School\Http\Requests\Logistics\StoreRoomRequest;
use Modules\School\Http\Requests\Logistics\StoreAssetRequest;

class FacilityController extends BaseController
{
    public function __construct(protected LogisticsService $service)
    {
    }

    public function overview(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', LandAsset::class);
        $schoolIdValue = $request->header('X-School-Id') ?? $request->input('school_id');
        $schoolId = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        $overview = $this->service->getFacilityOverview($schoolId);

        return $this->sendResponse($overview, 'Facility overview retrieved successfully.');
    }

    // --- Land Assets ---

    public function landAssets(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', LandAsset::class);
        $schoolIdValue = $request->header('X-School-Id') ?? $request->input('school_id');
        $schoolId = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        
        $perPageValue = $request->input('per_page', 20);
        $perPage = is_numeric($perPageValue) ? (int)$perPageValue : 20;
        
        $land = $this->service->getLandAssets($schoolId, $perPage);

        return $this->sendResponse($land, 'Land assets retrieved successfully.');
    }

    public function storeLand(StoreLandRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', LandAsset::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $land = $this->service->createLand($validated);

        return $this->sendResponse($land, 'Land asset created successfully.', 201);
    }

    public function updateLand(StoreLandRequest $request, int $id): \Illuminate\Http\JsonResponse
    {
        /** @var LandAsset $land */
        $land = LandAsset::findOrFail($id);
        $this->authorize('update', $land);
        
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $land = $this->service->updateLand($land, $validated);

        return $this->sendResponse($land, 'Land asset updated successfully.');
    }

    // --- Buildings ---

    public function buildings(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', Building::class);
        $schoolIdValue = $request->header('X-School-Id') ?? $request->input('school_id');
        $schoolId = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        
        $perPageValue = $request->input('per_page', 20);
        $perPage = is_numeric($perPageValue) ? (int)$perPageValue : 20;

        $buildings = $this->service->getBuildings($schoolId, $perPage);

        return $this->sendResponse($buildings, 'Buildings retrieved successfully.');
    }

    public function storeBuilding(StoreBuildingRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', Building::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $building = $this->service->createBuilding($validated);

        return $this->sendResponse($building, 'Building created successfully.', 201);
    }

    public function updateBuilding(StoreBuildingRequest $request, int $id): \Illuminate\Http\JsonResponse
    {
        /** @var Building $building */
        $building = Building::findOrFail($id);
        $this->authorize('update', $building);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $building = $this->service->updateBuilding($building, $validated);

        return $this->sendResponse($building, 'Building updated successfully.');
    }

    // --- Rooms ---

    public function rooms(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', Room::class);
        $schoolIdValue = $request->header('X-School-Id') ?? $request->input('school_id');
        $schoolId = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        
        $perPageValue = $request->input('per_page', 20);
        $perPage = is_numeric($perPageValue) ? (int)$perPageValue : 20;

        $rooms = $this->service->getRooms($schoolId, $perPage);

        return $this->sendResponse($rooms, 'Rooms retrieved successfully.');
    }

    public function storeRoom(StoreRoomRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', Room::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $room = $this->service->createRoom($validated);

        return $this->sendResponse($room, 'Room created successfully.', 201);
    }

    public function updateRoom(StoreRoomRequest $request, int $id): \Illuminate\Http\JsonResponse
    {
        /** @var Room $room */
        $room = Room::findOrFail($id);
        $this->authorize('update', $room);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $room = $this->service->updateRoom($room, $validated);

        return $this->sendResponse($room, 'Room updated successfully.');
    }

    // --- School Assets ---

    public function assets(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', SchoolAsset::class);
        $schoolIdValue = $request->header('X-School-Id') ?? $request->input('school_id');
        $schoolId = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        
        $perPageValue = $request->input('per_page', 20);
        $perPage = is_numeric($perPageValue) ? (int)$perPageValue : 20;

        $assets = $this->service->getAssets($schoolId, $perPage);

        return $this->sendResponse($assets, 'Assets retrieved successfully.');
    }

    public function storeAsset(StoreAssetRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', SchoolAsset::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $asset = $this->service->createAsset($validated);

        return $this->sendResponse($asset, 'Asset created successfully.', 201);
    }

    public function updateAsset(StoreAssetRequest $request, int $id): \Illuminate\Http\JsonResponse
    {
        /** @var SchoolAsset $asset */
        $asset = SchoolAsset::findOrFail($id);
        $this->authorize('update', $asset);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $asset = $this->service->updateAsset($asset, $validated);

        return $this->sendResponse($asset, 'Asset updated successfully.');
    }

    public function destroy(string $type, int $id): \Illuminate\Http\JsonResponse
    {
        /** @var \Illuminate\Database\Eloquent\Model|null $model */
        $model = match ($type) {
            'land' => LandAsset::findOrFail($id),
            'building' => Building::findOrFail($id),
            'room' => Room::findOrFail($id),
            'asset' => SchoolAsset::findOrFail($id),
            default => null
        };

        if (!$model) {
            return $this->sendError('Invalid facility type.', [], 400);
        }

        $this->authorize('delete', $model);
        /** @var \Illuminate\Database\Eloquent\Model $toDelete */
        $toDelete = $model;
        $toDelete->delete();

        return $this->sendResponse([], ucfirst($type).' deleted successfully.');
    }
}
