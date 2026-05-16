<?php

namespace Modules\Library\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Library\Models\FieldGroup;
use Modules\System\Http\Controllers\BaseApiController;

class FieldGroupController extends BaseApiController
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $groups = FieldGroup::with('fields')->get();
        return $this->success($groups, 'Field groups retrieved successfully');
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $group = FieldGroup::create($validated);

        if ($request->has('assignments')) {
            foreach ($request->input('assignments') as $assignment) {
                $group->assignments()->create($assignment);
            }
        }

        return $this->success($group->load('assignments'), 'Field group created successfully', 201);
    }

    public function show(FieldGroup $fieldGroup): \Illuminate\Http\JsonResponse
    {
        return $this->success($fieldGroup->load(['fields', 'assignments']), 'Field group retrieved successfully');
    }

    public function update(Request $request, FieldGroup $fieldGroup): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $fieldGroup->update($validated);

        if ($request->has('assignments')) {
            $fieldGroup->assignments()->delete();
            foreach ($request->input('assignments') as $assignment) {
                $fieldGroup->assignments()->create($assignment);
            }
        }

        return $this->success($fieldGroup->load('assignments'), 'Field group updated successfully');
    }

    public function destroy(FieldGroup $fieldGroup): \Illuminate\Http\JsonResponse
    {
        $fieldGroup->delete();
        return $this->success(null, 'Field group deleted successfully');
    }
}
