<?php

namespace Modules\School\Http\Controllers\Api\Logistics;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Logistics\LandAsset;
use Modules\School\Models\Logistics\Building;
use Modules\School\Models\Logistics\Room;
use Modules\School\Models\Logistics\SchoolAsset;
use Modules\School\Models\Logistics\MaintenanceTicket;
use Modules\School\Services\Logistics\LogisticsService;
use Modules\School\Http\Requests\Logistics\StoreMaintenanceTicketRequest;
use Modules\School\Http\Requests\Logistics\UpdateMaintenanceTicketRequest;

class SarprasController extends BaseController
{
    protected LogisticsService $service;

    public function __construct(LogisticsService $service)
    {
        $this->service = $service;
    }

    public function landAssets(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', LandAsset::class);
        /** @var \Illuminate\Support\Collection<int, LandAsset> $lands */
        $lands = LandAsset::all();
        return $this->sendResponse($lands, 'Land assets retrieved successfully.');
    }

    public function buildings(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', Building::class);
        /** @var \Illuminate\Support\Collection<int, Building> $buildings */
        $buildings = Building::with('landAsset')->get();
        return $this->sendResponse($buildings, 'Buildings retrieved successfully.');
    }

    public function rooms(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', Room::class);
        /** @var \Illuminate\Support\Collection<int, Room> $rooms */
        $rooms = Room::with('building')->get();
        return $this->sendResponse($rooms, 'Rooms retrieved successfully.');
    }

    public function assets(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', SchoolAsset::class);
        /** @var \Illuminate\Support\Collection<int, SchoolAsset> $assets */
        $assets = SchoolAsset::with('room')->get();
        return $this->sendResponse($assets, 'Assets retrieved successfully.');
    }

    // --- Maintenance Tickets ---

    public function tickets(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', MaintenanceTicket::class);
        /** @var array<string, mixed> $filters */
        $filters = $request->all();
        $perPageValue = $request->input('per_page', 20);
        $perPage = is_numeric($perPageValue) ? (int)$perPageValue : 20;
        $tickets = $this->service->getMaintenanceTickets($filters, $perPage);
        return $this->sendResponse($tickets, 'Maintenance tickets retrieved successfully.');
    }

    public function storeTicket(StoreMaintenanceTicketRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', MaintenanceTicket::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $ticket = $this->service->createMaintenanceTicket($validated);
        return $this->sendResponse($ticket, 'Maintenance ticket created successfully.', 201);
    }

    public function updateTicket(UpdateMaintenanceTicketRequest $request, int $id): \Illuminate\Http\JsonResponse
    {
        /** @var MaintenanceTicket $ticket */
        $ticket = MaintenanceTicket::findOrFail($id);
        $this->authorize('update', $ticket);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $ticket = $this->service->updateMaintenanceTicket($ticket, $validated);
        return $this->sendResponse($ticket, 'Maintenance ticket updated successfully.');
    }

    public function destroyTicket(int $id): \Illuminate\Http\JsonResponse
    {
        /** @var MaintenanceTicket $ticket */
        $ticket = MaintenanceTicket::findOrFail($id);
        $this->authorize('delete', $ticket);
        $ticket->delete();
        return $this->sendResponse([], 'Maintenance ticket deleted successfully.');
    }
}
