<?php

declare(strict_types=1);

namespace Modules\System\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\System\Http\Controllers\BaseApiController;
use Modules\System\Models\ContentType;
use Modules\System\Models\DynamicRecord;

class DynamicApiController extends BaseApiController
{
    /**
     * Get the dynamic content type by slug.
     */
    protected function getContentType(string $slug): ContentType
    {
        /** @var ContentType|null $contentType */
        $contentType = ContentType::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $contentType) {
            abort(404, "Dynamic content type '{$slug}' not found or inactive.");
        }

        return $contentType;
    }

    /**
     * Resolve validation rules dynamically based on content type fields.
     *
     * @return array<string, string>
     */
    protected function resolveValidationRules(ContentType $contentType): array
    {
        $rules = [];
        $fields = $contentType->fields;

        if (is_array($fields)) {
            foreach ($fields as $field) {
                if (! is_array($field)) {
                    continue;
                }

                $slugVal = $field['slug'] ?? '';
                $fieldSlug = is_scalar($slugVal) ? (string) $slugVal : '';
                if ($fieldSlug === '') {
                    continue;
                }

                $typeVal = $field['type'] ?? 'text';
                $fieldType = is_scalar($typeVal) ? (string) $typeVal : 'text';
                $isRequired = (bool) ($field['is_required'] ?? false);

                $ruleList = [];
                $ruleList[] = $isRequired ? 'required' : 'nullable';

                switch ($fieldType) {
                    case 'number':
                        $ruleList[] = 'numeric';
                        break;
                    case 'boolean':
                        $ruleList[] = 'boolean';
                        break;
                    case 'date':
                        $ruleList[] = 'date';
                        break;
                    default:
                        $ruleList[] = 'string';
                        break;
                }

                $rules[$fieldSlug] = implode('|', $ruleList);
            }
        }

        return $rules;
    }

    /**
     * GET /api/v1/dynamic/{slug}
     * List all dynamic records for a content type.
     */
    public function index(Request $request, string $slug): JsonResponse
    {
        $contentType = $this->getContentType($slug);

        $query = DynamicRecord::where('content_type_id', $contentType->id);

        // 1. Dynamic Searching (SQLite & MySQL compatible JSON search)
        $search = $request->query('search');
        if (is_string($search) && $search !== '') {
            $fields = $contentType->fields;
            $searchableFields = [];
            if (is_array($fields)) {
                foreach ($fields as $field) {
                    if (is_array($field)) {
                        $slugVal = $field['slug'] ?? '';
                        $searchableFields[] = is_scalar($slugVal) ? (string) $slugVal : '';
                    }
                }
            }

            if (! empty($searchableFields)) {
                $query->where(function ($q) use ($searchableFields, $search): void {
                    foreach ($searchableFields as $field) {
                        if ($field !== '') {
                            $q->orWhere("data->{$field}", 'like', "%{$search}%");
                        }
                    }
                });
            }
        }

        // 2. Dynamic Sorting
        $sortBy = $request->query('sort_by');
        $sortOrder = $request->query('sort_order', 'desc');
        if (is_string($sortBy) && $sortBy !== '') {
            $sortOrderClean = in_array(strtolower((string) $sortOrder), ['asc', 'desc']) ? $sortOrder : 'desc';
            $query->orderBy("data->{$sortBy}", $sortOrderClean);
        } else {
            $query->latest();
        }

        // 3. Paginate output
        $perPage = (int) $request->query('per_page', 15);
        $records = $query->paginate($perPage > 0 ? $perPage : 15);

        return $this->success($records, 'Dynamic records retrieved successfully');
    }

    /**
     * POST /api/v1/dynamic/{slug}
     * Create a new dynamic record.
     */
    public function store(Request $request, string $slug): JsonResponse
    {
        $contentType = $this->getContentType($slug);
        $rules = $this->resolveValidationRules($contentType);

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->error('Validasi Gagal', 422, $validator->errors()->toArray());
        }

        $payload = $validator->validated();

        $record = DynamicRecord::create([
            'content_type_id' => $contentType->id,
            'data' => $payload,
        ]);

        return $this->success($record, 'Dynamic record created successfully', 201);
    }

    /**
     * GET /api/v1/dynamic/{slug}/{id}
     * Get a single dynamic record by ID.
     */
    public function show(string $slug, string $id): JsonResponse
    {
        $contentType = $this->getContentType($slug);

        /** @var DynamicRecord|null $record */
        $record = DynamicRecord::where('content_type_id', $contentType->id)
            ->where('id', $id)
            ->first();

        if (! $record) {
            return $this->error('Record not found', 404);
        }

        return $this->success($record, 'Dynamic record retrieved successfully');
    }

    /**
     * PUT /api/v1/dynamic/{slug}/{id}
     * Update a dynamic record.
     */
    public function update(Request $request, string $slug, string $id): JsonResponse
    {
        $contentType = $this->getContentType($slug);

        /** @var DynamicRecord|null $record */
        $record = DynamicRecord::where('content_type_id', $contentType->id)
            ->where('id', $id)
            ->first();

        if (! $record) {
            return $this->error('Record not found', 404);
        }

        $rules = $this->resolveValidationRules($contentType);
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->error('Validasi Gagal', 422, $validator->errors()->toArray());
        }

        $payload = $validator->validated();

        // Merge updated fields with existing JSON data to support partial updates
        $existingData = $record->data;
        $updatedData = is_array($existingData) ? array_merge($existingData, $payload) : $payload;

        $record->update([
            'data' => $updatedData,
        ]);

        return $this->success($record, 'Dynamic record updated successfully');
    }

    /**
     * DELETE /api/v1/dynamic/{slug}/{id}
     * Delete a dynamic record.
     */
    public function destroy(string $slug, string $id): JsonResponse
    {
        $contentType = $this->getContentType($slug);

        /** @var DynamicRecord|null $record */
        $record = DynamicRecord::where('content_type_id', $contentType->id)
            ->where('id', $id)
            ->first();

        if (! $record) {
            return $this->error('Record not found', 404);
        }

        $record->delete();

        return $this->success(null, 'Dynamic record deleted successfully');
    }

    /**
     * GET /api/v1/manage/infra/cck/types
     * List all dynamic content type schemas.
     */
    public function listTypes(): JsonResponse
    {
        $types = ContentType::latest()->get();

        return $this->success($types, 'Content types retrieved successfully');
    }

    /**
     * POST /api/v1/manage/infra/cck/types
     * Create a new dynamic content type schema.
     */
    public function storeType(Request $request): JsonResponse
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:sys_content_types,slug',
            'description' => 'nullable|string',
            'fields' => 'required|array',
            'fields.*.name' => 'required|string',
            'fields.*.slug' => 'required|string',
            'fields.*.type' => 'required|string|in:text,longtext,number,boolean,date,image',
            'fields.*.is_required' => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->error('Validasi Gagal', 422, $validator->errors()->toArray());
        }

        $payload = $validator->validated();

        $type = ContentType::create([
            'name' => $payload['name'],
            'slug' => $payload['slug'],
            'description' => $payload['description'] ?? null,
            'fields' => $payload['fields'],
            'is_active' => true,
        ]);

        return $this->success($type, 'Content type created successfully', 201);
    }

    /**
     * GET /api/v1/manage/infra/cck/types/{id}
     * Get a single content type schema by ID.
     */
    public function showType(string $id): JsonResponse
    {
        $type = ContentType::find($id);

        if (! $type) {
            return $this->error('Content type not found', 404);
        }

        return $this->success($type, 'Content type retrieved successfully');
    }

    /**
     * PUT /api/v1/manage/infra/cck/types/{id}
     * Update an existing content type schema.
     */
    public function updateType(Request $request, string $id): JsonResponse
    {
        $type = ContentType::find($id);

        if (! $type) {
            return $this->error('Content type not found', 404);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:sys_content_types,slug,'.$id,
            'description' => 'nullable|string',
            'fields' => 'required|array',
            'fields.*.name' => 'required|string',
            'fields.*.slug' => 'required|string',
            'fields.*.type' => 'required|string|in:text,longtext,number,boolean,date,image',
            'fields.*.is_required' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->error('Validasi Gagal', 422, $validator->errors()->toArray());
        }

        $payload = $validator->validated();

        $type->update([
            'name' => $payload['name'],
            'slug' => $payload['slug'],
            'description' => $payload['description'] ?? null,
            'fields' => $payload['fields'],
            'is_active' => isset($payload['is_active']) ? (bool) $payload['is_active'] : $type->is_active,
        ]);

        return $this->success($type, 'Content type updated successfully');
    }

    /**
     * DELETE /api/v1/manage/infra/cck/types/{id}
     * Delete an existing content type schema.
     */
    public function destroyType(string $id): JsonResponse
    {
        $type = ContentType::find($id);

        if (! $type) {
            return $this->error('Content type not found', 404);
        }

        $type->delete();

        return $this->success(null, 'Content type deleted successfully');
    }
}
