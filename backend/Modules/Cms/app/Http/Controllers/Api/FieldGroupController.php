<?php

namespace Modules\Cms\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Cms\Models\FieldGroup;
use Modules\Core\Http\Controllers\Api\BaseApiController;

class FieldGroupController extends BaseApiController
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = FieldGroup::with('fields')->withCount('fields');

        if ($request->has('applies_to')) {
            $appliesToRaw = $request->applies_to;
            $appliesTo = is_string($appliesToRaw) ? $appliesToRaw : '';
            $query->where('applies_to', $appliesTo);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $groups = $query->orderBy('sort_order')->get();

        return $this->success($groups, 'Field groups retrieved successfully');
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:field_groups,slug',
            'description' => 'nullable|string',
            'applies_to' => 'required|string',
            'conditions' => 'nullable|array',
            'sort_order' => 'integer',
        ]);

        $group = FieldGroup::create($validated);

        return $this->success($group->load('fields'), 'Field group created successfully', 201);
    }

    public function show(FieldGroup $fieldGroup): \Illuminate\Http\JsonResponse
    {
        return $this->success($fieldGroup->load('fields'), 'Field group retrieved successfully');
    }

    public function update(Request $request, FieldGroup $fieldGroup): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:field_groups,slug,'.$fieldGroup->id,
            'description' => 'nullable|string',
            'applies_to' => 'sometimes|required|string',
            'conditions' => 'nullable|array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $fieldGroup->update($validated);

        return $this->success($fieldGroup->load('fields'), 'Field group updated successfully');
    }

    public function destroy(FieldGroup $fieldGroup): \Illuminate\Http\JsonResponse
    {
        $fieldGroup->delete();

        return $this->success(null, 'Field group deleted successfully');
    }
}
