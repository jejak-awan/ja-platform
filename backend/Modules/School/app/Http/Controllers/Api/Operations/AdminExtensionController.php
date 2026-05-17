<?php

namespace Modules\School\Http\Controllers\Api\Operations;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Operations\LibraryBook;
use Modules\School\Models\Operations\LibraryCirculation;
use Modules\School\Models\Operations\UksVisit;
use Modules\School\Models\Operations\GuestLog;
use Modules\School\Models\Student\Alumni;
use Modules\School\Models\Student\TracerStudy;
use Modules\School\Services\Operations\OperationsService;
use Modules\School\Http\Requests\Operations\StoreLibraryBookRequest;
use Modules\School\Http\Requests\Operations\StoreLibraryCirculationRequest;
use Modules\School\Http\Requests\Operations\StoreUksVisitRequest;
use Modules\School\Http\Requests\Operations\StoreGuestLogRequest;
use Modules\School\Http\Requests\Operations\StoreAlumniRequest;
use Modules\School\Http\Requests\Operations\StoreTracerStudyRequest;

class AdminExtensionController extends BaseController
{
    public function __construct(protected OperationsService $service)
    {
    }

    public function overview(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', LibraryBook::class);
        return $this->sendResponse([
            'library_count' => LibraryBook::count(),
            'library_circulation_count' => LibraryCirculation::count(),
            'uks_count' => UksVisit::count(),
            'guest_count' => GuestLog::count(),
            'alumni_count' => Alumni::count(),
            'tracer_count' => TracerStudy::count(),
        ], 'Admin extensions overview retrieved successfully.');
    }

    // --- Library ---

    public function libraryBooks(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', LibraryBook::class);
        $books = $this->service->getLibraryBooks();
        return $this->sendResponse($books, 'Library books retrieved successfully.');
    }

    public function storeLibraryBook(StoreLibraryBookRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', LibraryBook::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $book = $this->service->createLibraryBook($validated);
        return $this->sendResponse($book, 'Library book created successfully.', 201);
    }

    public function updateLibraryBook(StoreLibraryBookRequest $request, string $id): \Illuminate\Http\JsonResponse
    {
        /** @var LibraryBook $book */
        $book = LibraryBook::findOrFail($id);
        $this->authorize('update', $book);
        
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $book = $this->service->updateLibraryBook($book, $validated);
        return $this->sendResponse($book, 'Library book updated successfully.');
    }

    public function destroyLibraryBook(string $id): \Illuminate\Http\JsonResponse
    {
        /** @var LibraryBook $book */
        $book = LibraryBook::findOrFail($id);
        $this->authorize('delete', $book);
        $book->delete();
        return $this->sendResponse([], 'Library book deleted successfully.');
    }

    public function libraryCirculations(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', LibraryCirculation::class);
        $circulations = $this->service->getLibraryCirculations();
        return $this->sendResponse($circulations, 'Library circulations retrieved successfully.');
    }

    public function storeLibraryCirculation(StoreLibraryCirculationRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', LibraryCirculation::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $circulation = $this->service->createLibraryCirculation($validated);
        return $this->sendResponse($circulation, 'Book borrowed successfully.', 201);
    }

    public function updateLibraryCirculation(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        /** @var LibraryCirculation $circulation */
        $circulation = LibraryCirculation::findOrFail($id);
        $this->authorize('update', $circulation);

        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'return_date' => 'nullable|date',
            'fine_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:Borrowed,Returned,Overdue,Lost',
        ]);

        $circulation = $this->service->updateLibraryCirculation($circulation, $validated);
        return $this->sendResponse($circulation, 'Circulation record updated successfully.');
    }

    public function destroyLibraryCirculation(string $id): \Illuminate\Http\JsonResponse
    {
        /** @var LibraryCirculation $circulation */
        $circulation = LibraryCirculation::findOrFail($id);
        $this->authorize('delete', $circulation);
        $circulation->delete();
        return $this->sendResponse([], 'Circulation record deleted successfully.');
    }

    // --- UKS Visits ---

    public function uksVisits(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', UksVisit::class);
        $visits = $this->service->getUksVisits();
        return $this->sendResponse($visits, 'UKS visits retrieved successfully.');
    }

    public function storeUksVisit(StoreUksVisitRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', UksVisit::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $visit = $this->service->createUksVisit($validated);
        return $this->sendResponse($visit, 'UKS visit recorded successfully.', 201);
    }

    public function updateUksVisit(StoreUksVisitRequest $request, string $id): \Illuminate\Http\JsonResponse
    {
        /** @var UksVisit $visit */
        $visit = UksVisit::findOrFail($id);
        $this->authorize('update', $visit);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $visit = $this->service->updateUksVisit($visit, $validated);
        return $this->sendResponse($visit, 'UKS visit updated successfully.');
    }

    public function destroyUksVisit(string $id): \Illuminate\Http\JsonResponse
    {
        /** @var UksVisit $visit */
        $visit = UksVisit::findOrFail($id);
        $this->authorize('delete', $visit);
        $visit->delete();
        return $this->sendResponse([], 'UKS visit records deleted successfully.');
    }

    // --- Guest Logs ---

    public function guestLogs(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', GuestLog::class);
        $logs = $this->service->getGuestLogs();
        return $this->sendResponse($logs, 'Guest logs retrieved successfully.');
    }

    public function storeGuestLog(StoreGuestLogRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', GuestLog::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $log = $this->service->createGuestLog($validated);
        return $this->sendResponse($log, 'Guest log recorded successfully.', 201);
    }

    public function updateGuestLog(StoreGuestLogRequest $request, string $id): \Illuminate\Http\JsonResponse
    {
        /** @var GuestLog $log */
        $log = GuestLog::findOrFail($id);
        $this->authorize('update', $log);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $log = $this->service->updateGuestLog($log, $validated);
        return $this->sendResponse($log, 'Guest log updated successfully.');
    }

    public function destroyGuestLog(string $id): \Illuminate\Http\JsonResponse
    {
        /** @var GuestLog $log */
        $log = GuestLog::findOrFail($id);
        $this->authorize('delete', $log);
        $log->delete();
        return $this->sendResponse([], 'Guest log deleted successfully.');
    }

    // --- Alumni ---

    public function alumni(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', Alumni::class);
        $alumni = $this->service->getAlumni();
        return $this->sendResponse($alumni, 'Alumni records retrieved successfully.');
    }

    public function storeAlumni(StoreAlumniRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', Alumni::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $alumnus = $this->service->createAlumni($validated);
        return $this->sendResponse($alumnus, 'Alumni record created successfully.', 201);
    }

    public function updateAlumni(StoreAlumniRequest $request, string $id): \Illuminate\Http\JsonResponse
    {
        /** @var Alumni $alumnus */
        $alumnus = Alumni::findOrFail($id);
        $this->authorize('update', $alumnus);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $alumnus = $this->service->updateAlumni($alumnus, $validated);
        return $this->sendResponse($alumnus, 'Alumni record updated successfully.');
    }

    public function destroyAlumni(string $id): \Illuminate\Http\JsonResponse
    {
        /** @var Alumni $alumnus */
        $alumnus = Alumni::findOrFail($id);
        $this->authorize('delete', $alumnus);
        $alumnus->delete();
        return $this->sendResponse([], 'Alumni record deleted successfully.');
    }

    public function tracerStudies(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', TracerStudy::class);
        $studies = $this->service->getTracerStudies();
        return $this->sendResponse($studies, 'Tracer studies retrieved successfully.');
    }

    public function storeTracerStudy(StoreTracerStudyRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', TracerStudy::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $study = $this->service->createTracerStudy($validated);
        return $this->sendResponse($study, 'Tracer study recorded successfully.', 201);
    }

    public function updateTracerStudy(StoreTracerStudyRequest $request, string $id): \Illuminate\Http\JsonResponse
    {
        /** @var TracerStudy $study */
        $study = TracerStudy::findOrFail($id);
        $this->authorize('update', $study);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $study = $this->service->updateTracerStudy($study, $validated);
        return $this->sendResponse($study, 'Tracer study updated successfully.');
    }

    public function destroyTracerStudy(string $id): \Illuminate\Http\JsonResponse
    {
        /** @var TracerStudy $study */
        $study = TracerStudy::findOrFail($id);
        $this->authorize('delete', $study);
        $study->delete();
        return $this->sendResponse([], 'Tracer study record deleted successfully.');
    }
}
