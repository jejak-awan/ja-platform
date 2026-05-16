<?php

namespace Modules\Library\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Library\Models\CustomField;
use Modules\System\Http\Controllers\BaseApiController;

class CustomFieldController extends BaseApiController
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = CustomField::with('groups');

        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        $fields = $query->orderBy('sort_order')->get();

        return $this->success($fields, 'Custom fields retrieved successfully');
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255',
            'type' => 'required|string',
            'options' => 'nullable|array',
            'rules' => 'nullable|array',
            'default_value' => 'nullable|string',
            'placeholder' => 'nullable|string',
            'help_text' => 'nullable|string',
            'is_required' => 'boolean',
            'is_filterable' => 'boolean',
            'sort_order' => 'integer',
        ]);

        /** @var \Modules\System\Models\User|null $user */
        $user = $request->user();
        if ($user) {
            $validated['author_id'] = $user->id;
        }

        $field = CustomField::create($validated);

        if ($request->has('group_ids')) {
            $field->groups()->sync($request->input('group_ids'));
        }

        return $this->success($field->load('groups'), 'Custom field created successfully', 201);
    }

    public function show(CustomField $customField): \Illuminate\Http\JsonResponse
    {
        return $this->success($customField->load('groups'), 'Custom field retrieved successfully');
    }

    public function update(Request $request, CustomField $customField): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'key' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string',
            'options' => 'nullable|array',
            'rules' => 'nullable|array',
            'default_value' => 'nullable|string',
            'placeholder' => 'nullable|string',
            'help_text' => 'nullable|string',
            'is_required' => 'boolean',
            'is_filterable' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $customField->update($validated);

        if ($request->has('group_ids')) {
            $customField->groups()->sync($request->input('group_ids'));
        }

        return $this->success($customField->load('groups'), 'Custom field updated successfully');
    }

    public function destroy(CustomField $customField): \Illuminate\Http\JsonResponse
    {
        $customField->delete();
        return $this->success(null, 'Custom field deleted successfully');
    }
}
