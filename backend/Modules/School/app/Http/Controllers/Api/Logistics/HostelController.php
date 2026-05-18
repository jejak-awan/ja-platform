<?php

namespace Modules\School\Http\Controllers\Api\Logistics;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Http\Requests\Logistics\AllocateBedRequest;
use Modules\School\Http\Requests\Logistics\StoreHostelBlockRequest;
use Modules\School\Http\Requests\Logistics\StoreHostelRoomRequest;
use Modules\School\Models\Logistics\HostelAllocation;
use Modules\School\Models\Logistics\HostelBed;
use Modules\School\Models\Logistics\HostelBlock;
use Modules\School\Models\Logistics\HostelRoom;
use Modules\School\Services\Logistics\LogisticsService;

class HostelController extends BaseController
{
    public function __construct(protected LogisticsService $service) {}

    public function blocks(): JsonResponse
    {
        $this->authorize('viewAny', HostelBlock::class);
        $blocks = $this->service->getHostelBlocks();

        return $this->sendResponse($blocks, 'Hostel blocks retrieved successfully.');
    }

    public function storeBlock(StoreHostelBlockRequest $request): JsonResponse
    {
        $this->authorize('create', HostelBlock::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $block = $this->service->createHostelBlock($validated);

        return $this->sendResponse($block, 'Hostel block created successfully.', 201);
    }

    public function rooms(int $blockId): JsonResponse
    {
        $this->authorize('viewAny', HostelRoom::class);
        /** @var Collection<int, HostelRoom> $rooms */
        $rooms = HostelRoom::where('block_id', $blockId)->withCount('beds')->get();

        return $this->sendResponse($rooms, 'Hostel rooms retrieved successfully.');
    }

    public function storeRoom(StoreHostelRoomRequest $request, int $blockId): JsonResponse
    {
        /** @var HostelBlock $block */
        $block = HostelBlock::findOrFail($blockId);
        $this->authorize('update', $block);

        try {
            /** @var array<string, mixed> $validated */
            $validated = $request->validated();
            $room = $this->service->createHostelRoom($block, $validated);

            return $this->sendResponse($room, 'Room created and beds generated successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    public function beds(int $roomId): JsonResponse
    {
        $this->authorize('viewAny', HostelBed::class);
        /** @var Collection<int, HostelBed> $beds */
        $beds = HostelBed::where('room_id', $roomId)->with('allocation.student')->get();

        return $this->sendResponse($beds, 'Hostel beds retrieved successfully.');
    }

    public function allocate(AllocateBedRequest $request): JsonResponse
    {
        $this->authorize('create', HostelAllocation::class);

        try {
            /** @var array<string, mixed> $validated */
            $validated = $request->validated();
            $allocation = $this->service->allocateBed($validated);

            return $this->sendResponse($allocation, 'Student allocated to bed successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    public function release(string $id): JsonResponse
    {
        /** @var HostelAllocation $allocation */
        $allocation = HostelAllocation::findOrFail($id);
        $this->authorize('delete', $allocation);

        $this->service->releaseBed($id);

        return $this->sendResponse([], 'Student released from bed successfully.');
    }
}
