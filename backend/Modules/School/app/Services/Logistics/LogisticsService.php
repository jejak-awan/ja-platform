<?php

namespace Modules\School\Services\Logistics;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\School\Models\Logistics\Building;
use Modules\School\Models\Logistics\HostelAllocation;
use Modules\School\Models\Logistics\HostelBed;
use Modules\School\Models\Logistics\HostelBlock;
use Modules\School\Models\Logistics\HostelRoom;
use Modules\School\Models\Logistics\InventoryCategory;
use Modules\School\Models\Logistics\InventoryItem;
use Modules\School\Models\Logistics\InventoryTransaction;
use Modules\School\Models\Logistics\LandAsset;
use Modules\School\Models\Logistics\MaintenanceTicket;
use Modules\School\Models\Logistics\Room;
use Modules\School\Models\Logistics\SchoolAsset;
use Modules\School\Models\Logistics\TransportRegistration;
use Modules\School\Models\Logistics\Vehicle;
use Modules\School\Models\Logistics\VehicleRoute;

class LogisticsService
{
    /**
     * Get facility overview.
     *
     * @return array{land_count: int, building_count: int, room_count: int, asset_count: int}
     */
    public function getFacilityOverview(string $schoolId): array
    {
        return [
            'land_count' => (int) LandAsset::where('school_id', $schoolId)->count(),
            'building_count' => (int) Building::whereHas('landAsset', function ($q) use ($schoolId): void {
                $q->where('school_id', $schoolId);
            })->count(),
            'room_count' => (int) Room::whereHas('building.landAsset', function ($q) use ($schoolId): void {
                $q->where('school_id', $schoolId);
            })->count(),
            'asset_count' => (int) SchoolAsset::whereHas('room.building.landAsset', function ($q) use ($schoolId): void {
                $q->where('school_id', $schoolId);
            })->count(),
        ];
    }

    /**
     * Get land assets with pagination.
     *
     * @return LengthAwarePaginator<int, LandAsset>
     */
    public function getLandAssets(string $schoolId, int $perPage = 20): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, LandAsset> $paginator */
        $paginator = LandAsset::where('school_id', $schoolId)->paginate($perPage);

        return $paginator;
    }

    /**
     * Create land asset.
     *
     * @param  array<string, mixed>  $data
     */
    public function createLand(array $data): LandAsset
    {
        /** @var LandAsset $land */
        $land = LandAsset::create($data);

        return $land;
    }

    /**
     * Update land asset.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateLand(LandAsset $land, array $data): LandAsset
    {
        $land->update($data);

        return $land;
    }

    /**
     * Get buildings with pagination.
     *
     * @return LengthAwarePaginator<int, Building>
     */
    public function getBuildings(string $schoolId, int $perPage = 20): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Building> $paginator */
        $paginator = Building::whereHas('landAsset', function ($q) use ($schoolId): void {
            $q->where('school_id', $schoolId);
        })->with('landAsset')->paginate($perPage);

        return $paginator;
    }

    /**
     * Create building.
     *
     * @param  array<string, mixed>  $data
     */
    public function createBuilding(array $data): Building
    {
        /** @var Building $building */
        $building = Building::create($data);

        return $building;
    }

    /**
     * Update building.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateBuilding(Building $building, array $data): Building
    {
        $building->update($data);

        return $building;
    }

    /**
     * Get rooms with pagination.
     *
     * @return LengthAwarePaginator<int, Room>
     */
    public function getRooms(string $schoolId, int $perPage = 20): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Room> $paginator */
        $paginator = Room::whereHas('building.landAsset', function ($q) use ($schoolId): void {
            $q->where('school_id', $schoolId);
        })->with('building')->paginate($perPage);

        return $paginator;
    }

    /**
     * Create room.
     *
     * @param  array<string, mixed>  $data
     */
    public function createRoom(array $data): Room
    {
        /** @var Room $room */
        $room = Room::create($data);

        return $room;
    }

    /**
     * Update room.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateRoom(Room $room, array $data): Room
    {
        $room->update($data);

        return $room;
    }

    /**
     * Get school assets with pagination.
     *
     * @return LengthAwarePaginator<int, SchoolAsset>
     */
    public function getAssets(string $schoolId, int $perPage = 20): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, SchoolAsset> $paginator */
        $paginator = SchoolAsset::whereHas('room.building.landAsset', function ($q) use ($schoolId): void {
            $q->where('school_id', $schoolId);
        })->with(['room.building'])->paginate($perPage);

        return $paginator;
    }

    /**
     * Create school asset.
     *
     * @param  array<string, mixed>  $data
     */
    public function createAsset(array $data): SchoolAsset
    {
        /** @var SchoolAsset $asset */
        $asset = SchoolAsset::create($data);

        return $asset;
    }

    /**
     * Update school asset.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateAsset(SchoolAsset $asset, array $data): SchoolAsset
    {
        $asset->update($data);

        return $asset;
    }

    // --- Inventory ---

    /**
     * Get inventory categories.
     *
     * @return Collection<int, InventoryCategory>
     */
    public function getInventoryCategories(): Collection
    {
        /** @var Collection<int, InventoryCategory> $categories */
        $categories = InventoryCategory::withCount('items')->get();

        return $categories;
    }

    /**
     * Get inventory items with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, InventoryItem>
     */
    public function getInventoryItems(array $filters): Collection
    {
        /** @var Collection<int, InventoryItem> $items */
        $items = InventoryItem::with('category')
            ->when(! empty($filters['category_id']), fn ($q) => $q->where('category_id', $filters['category_id']))
            ->when(! empty($filters['search']) && is_string($filters['search']), function ($q) use ($filters) {
                /** @var string $search */
                $search = $filters['search'];
                $searchStr = strtolower($search);

                return $q->where(DB::raw('lower(name)'), 'like', "%{$searchStr}%")
                    ->orWhere(DB::raw('lower(sku)'), 'like', "%{$searchStr}%");
            })
            ->get();

        return $items;
    }

    /**
     * Create inventory item.
     *
     * @param  array<string, mixed>  $data
     */
    public function createInventoryItem(array $data): InventoryItem
    {
        /** @var InventoryItem $item */
        $item = InventoryItem::create($data);

        return $item;
    }

    /**
     * Adjust inventory stock.
     *
     * @param  array<string, mixed>  $data
     */
    public function adjustInventoryStock(array $data): InventoryTransaction
    {
        /** @var InventoryTransaction $transaction */
        $transaction = DB::transaction(function () use ($data) {
            /** @var InventoryItem $item */
            $item = InventoryItem::findOrFail($data['item_id']);

            $quantity = is_numeric($data['quantity']) ? (int) $data['quantity'] : 0;
            if ($data['type'] === 'in' || $data['type'] === 'adjustment') {
                $item->increment('quantity_on_hand', $quantity);
            } else {
                if ((int) $item->quantity_on_hand < $quantity) {
                    throw new \Exception('Insufficient stock.');
                }
                $item->decrement('quantity_on_hand', $quantity);
            }

            /** @var InventoryTransaction $transaction */
            $transaction = InventoryTransaction::create(array_merge($data, [
                'school_id' => $item->school_id,
                'reference_number' => 'TRX-'.strtoupper(uniqid()),
            ]));

            return $transaction;
        });

        return $transaction;
    }

    // --- Hostel ---

    /**
     * Get hostel blocks.
     *
     * @return Collection<int, HostelBlock>
     */
    public function getHostelBlocks(): Collection
    {
        /** @var Collection<int, HostelBlock> $blocks */
        $blocks = HostelBlock::withCount('rooms')->get();

        return $blocks;
    }

    /**
     * Create hostel block.
     *
     * @param  array<string, mixed>  $data
     */
    public function createHostelBlock(array $data): HostelBlock
    {
        /** @var HostelBlock $block */
        $block = HostelBlock::create($data);

        return $block;
    }

    /**
     * Create hostel room and auto-generate beds.
     *
     * @param  array<string, mixed>  $data
     */
    public function createHostelRoom(HostelBlock $block, array $data): HostelRoom
    {
        /** @var HostelRoom $roomResult */
        $roomResult = DB::transaction(function () use ($block, $data) {
            /** @var HostelRoom $room */
            $room = $block->rooms()->create($data);

            $capacity = isset($data['capacity']) && is_numeric($data['capacity']) ? (int) $data['capacity'] : 0;
            for ($i = 1; $i <= $capacity; $i++) {
                $room->beds()->create([
                    'bed_number' => $room->room_number.'-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                    'is_available' => true,
                ]);
            }

            return $room->load('beds');
        });

        return $roomResult;
    }

    /**
     * Allocate bed to student.
     *
     * @param  array<string, mixed>  $data
     */
    public function allocateBed(array $data): HostelAllocation
    {
        /** @var HostelAllocation $allocationResult */
        $allocationResult = DB::transaction(function () use ($data) {
            /** @var HostelBed $bed */
            $bed = HostelBed::findOrFail($data['bed_id']);

            if (! $bed->is_available) {
                throw new \Exception('Bed is already occupied.');
            }

            /** @var HostelAllocation $allocation */
            $allocation = HostelAllocation::create($data);
            $bed->update(['is_available' => false]);

            return $allocation;
        });

        return $allocationResult;
    }

    /**
     * Release student from bed.
     */
    public function releaseBed(string $allocationId): void
    {
        DB::transaction(function () use ($allocationId): void {
            /** @var HostelAllocation $allocation */
            $allocation = HostelAllocation::findOrFail($allocationId);
            $allocation->update([
                'status' => 'completed',
                'end_date' => now()->toDateString(),
            ]);

            /** @var HostelBed $bed */
            $bed = $allocation->bed;
            $bed->update(['is_available' => true]);
            $allocation->delete();
        });
    }

    // --- Transport ---

    /**
     * Get vehicles.
     *
     * @return Collection<int, Vehicle>
     */
    public function getVehicles(): Collection
    {
        /** @var Collection<int, Vehicle> $vehicles */
        $vehicles = Vehicle::withCount('registrations')->get();

        return $vehicles;
    }

    /**
     * Create vehicle.
     *
     * @param  array<string, mixed>  $data
     */
    public function createVehicle(array $data): Vehicle
    {
        /** @var Vehicle $vehicle */
        $vehicle = Vehicle::create($data);

        return $vehicle;
    }

    /**
     * Get transport routes.
     *
     * @return Collection<int, VehicleRoute>
     */
    public function getTransportRoutes(): Collection
    {
        /** @var Collection<int, VehicleRoute> $routes */
        $routes = VehicleRoute::all();

        return $routes;
    }

    /**
     * Create transport route.
     *
     * @param  array<string, mixed>  $data
     */
    public function createTransportRoute(array $data): VehicleRoute
    {
        /** @var VehicleRoute $route */
        $route = VehicleRoute::create($data);

        return $route;
    }

    /**
     * Register student for transport.
     *
     * @param  array<string, mixed>  $data
     */
    public function registerTransportStudent(array $data): TransportRegistration
    {
        /** @var TransportRegistration $registration */
        $registration = TransportRegistration::create($data);

        return $registration;
    }

    // --- Maintenance (Sarpras) ---

    /**
     * Get maintenance tickets with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, MaintenanceTicket>
     */
    public function getMaintenanceTickets(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, MaintenanceTicket> $paginator */
        $paginator = MaintenanceTicket::with(['asset', 'reporter'])
            ->when(! empty($filters['status']) && is_string($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->when(! empty($filters['asset_id']), fn ($q) => $q->where('school_asset_id', $filters['asset_id']))
            ->latest()
            ->paginate($perPage);

        return $paginator;
    }

    /**
     * Create maintenance ticket.
     *
     * @param  array<string, mixed>  $data
     */
    public function createMaintenanceTicket(array $data): MaintenanceTicket
    {
        /** @var MaintenanceTicket $ticket */
        $ticket = MaintenanceTicket::create($data);

        return $ticket;
    }

    /**
     * Update maintenance ticket.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateMaintenanceTicket(MaintenanceTicket $ticket, array $data): MaintenanceTicket
    {
        if (isset($data['status']) && $data['status'] === 'Resolved' && ! isset($data['date_resolved'])) {
            $data['date_resolved'] = now();
        }

        $ticket->update($data);

        return $ticket;
    }
}
