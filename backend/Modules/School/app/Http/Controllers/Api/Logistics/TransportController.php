<?php

namespace Modules\School\Http\Controllers\Api\Logistics;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Http\Requests\Logistics\RegisterTransportStudentRequest;
use Modules\School\Http\Requests\Logistics\StoreTransportRouteRequest;
use Modules\School\Http\Requests\Logistics\StoreVehicleRequest;
use Modules\School\Models\Logistics\TransportRegistration;
use Modules\School\Models\Logistics\Vehicle;
use Modules\School\Models\Logistics\VehicleRoute;
use Modules\School\Services\Logistics\LogisticsService;

class TransportController extends BaseController
{
    public function __construct(protected LogisticsService $service) {}

    public function vehicles(): JsonResponse
    {
        $this->authorize('viewAny', Vehicle::class);
        $vehicles = $this->service->getVehicles();

        return $this->sendResponse($vehicles, 'Vehicles retrieved successfully.');
    }

    public function storeVehicle(StoreVehicleRequest $request): JsonResponse
    {
        $this->authorize('create', Vehicle::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $vehicle = $this->service->createVehicle($validated);

        return $this->sendResponse($vehicle, 'Vehicle registered successfully.', 201);
    }

    public function routes(): JsonResponse
    {
        $this->authorize('viewAny', VehicleRoute::class);
        $routes = $this->service->getTransportRoutes();

        return $this->sendResponse($routes, 'Routes retrieved successfully.');
    }

    public function storeRoute(StoreTransportRouteRequest $request): JsonResponse
    {
        $this->authorize('create', VehicleRoute::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $route = $this->service->createTransportRoute($validated);

        return $this->sendResponse($route, 'Route created successfully.', 201);
    }

    public function registrations(): JsonResponse
    {
        $this->authorize('viewAny', TransportRegistration::class);
        /** @var Collection<int, TransportRegistration> $registrations */
        $registrations = TransportRegistration::with(['student', 'vehicle', 'route'])->latest()->get();

        return $this->sendResponse($registrations, 'Transport registrations retrieved successfully.');
    }

    public function registerStudent(RegisterTransportStudentRequest $request): JsonResponse
    {
        $this->authorize('create', TransportRegistration::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $registration = $this->service->registerTransportStudent($validated);

        return $this->sendResponse($registration, 'Student registered for transport successfully.', 201);
    }
}
