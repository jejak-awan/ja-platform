<?php

namespace Modules\School\Http\Controllers\Api\Operations;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Operations\DocumentTemplate;

class DocumentTemplateController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $schoolId = $this->resolveSchoolId($request);
        $templates = DocumentTemplate::where('school_id', $schoolId)
            ->latest()
            ->get();

        return $this->sendResponse($templates, 'Templates retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string|in:certificate,skl,letter',
            'content' => 'required|string',
            'styles' => 'nullable|string',
            'placeholders' => 'nullable|array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $validated['school_id'] = $this->resolveSchoolId($request);

        // If is_default is true, unset other defaults of same type
        if (! empty($validated['is_default'])) {
            DocumentTemplate::where('school_id', $validated['school_id'])
                ->where('type', $validated['type'])
                ->update(['is_default' => false]);
        }

        $template = DocumentTemplate::create($validated);

        return $this->sendResponse($template, 'Template created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $template = DocumentTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string|in:certificate,skl,letter',
            'content' => 'required|string',
            'styles' => 'nullable|string',
            'placeholders' => 'nullable|array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        if (! empty($validated['is_default'])) {
            DocumentTemplate::where('school_id', $template->school_id)
                ->where('type', $validated['type'])
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $template->update($validated);

        return $this->sendResponse($template, 'Template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $template = DocumentTemplate::findOrFail($id);
        $template->delete();

        return $this->sendResponse(null, 'Template deleted successfully.');
    }

    /**
     * Resolve the school ID from the request context.
     */
    protected function resolveSchoolId(Request $request): string
    {
        $schoolId = $request->input('school_id') ?? $request->header('X-School-Id');

        if (is_scalar($schoolId) && $schoolId !== '') {
            return (string) $schoolId;
        }

        /** @var School|null $defaultSchool */
        $defaultSchool = School::first();
        if (! $defaultSchool) {
            return '1';
        }

        return (string) $defaultSchool->id;
    }
}
