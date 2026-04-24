<?php

namespace Modules\School\Http\Controllers\Api\Logistics;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Logistics\Vehicle;
use Modules\School\Models\Logistics\VehicleRoute;
use Modules\School\Models\Logistics\TransportRegistration;
use Modules\School\Services\Logistics\LogisticsService;
use Modules\School\Http\Requests\Logistics\StoreVehicleRequest;
use Modules\School\Http\Requests\Logistics\StoreTransportRouteRequest;
use Modules\School\Http\Requests\Logistics\RegisterTransportStudentRequest;

class TransportController extends BaseController
{
    protected LogisticsService $service;

    public function __construct(LogisticsService $service)
    {
        $this->service = $service;
    }

    public function vehicles(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', Vehicle::class);
        $vehicles = $this->service->getVehicles();
        return $this->sendResponse($vehicles, 'Vehicles retrieved successfully.');
    }

    public function storeVehicle(StoreVehicleRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', Vehicle::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $vehicle = $this->service->createVehicle($validated);
        return $this->sendResponse($vehicle, 'Vehicle registered successfully.', 201);
    }

    public function routes(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', VehicleRoute::class);
        $routes = $this->service->getTransportRoutes();
        return $this->sendResponse($routes, 'Routes retrieved successfully.');
    }

    public function storeRoute(StoreTransportRouteRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', VehicleRoute::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $route = $this->service->createTransportRoute($validated);
        return $this->sendResponse($route, 'Route created successfully.', 201);
    }

    public function registrations(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', TransportRegistration::class);
        /** @var \Illuminate\Support\Collection<int, TransportRegistration> $registrations */
        $registrations = TransportRegistration::with(['student', 'vehicle', 'route'])->latest()->get();
        return $this->sendResponse($registrations, 'Transport registrations retrieved successfully.');
    }

    public function registerStudent(RegisterTransportStudentRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', TransportRegistration::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $registration = $this->service->registerTransportStudent($validated);
        return $this->sendResponse($registration, 'Student registered for transport successfully.', 201);
    }
}
