<?php

namespace Modules\School\Http\Controllers\Api\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\School\Models\Operations\GraduationSetting;

class GraduationSettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = GraduationSetting::orderBy('graduation_year', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $settings
        ]);
    }

    /**
     * @param int|string $year
     */
    public function show($year): JsonResponse
    {
        $setting = GraduationSetting::where('graduation_year', $year)->firstOrFail();
        return response()->json([
            'status' => 'success',
            'data' => $setting
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'graduation_year' => 'required|integer|unique:sch_grad_settings,graduation_year',
            'is_open' => 'boolean',
            'announcement_date' => 'nullable|date',
            'subjects' => 'nullable|array',
            'config' => 'nullable|array',
        ]);

        $setting = GraduationSetting::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Graduation setting created successfully.',
            'data' => $setting
        ]);
    }

    /**
     * @param int|string $year
     */
    public function update(Request $request, $year): JsonResponse
    {
        $setting = GraduationSetting::where('graduation_year', $year)->firstOrFail();

        $request->validate([
            'is_open' => 'boolean',
            'announcement_date' => 'nullable|date',
            'subjects' => 'nullable|array',
            'config' => 'nullable|array',
        ]);

        $setting->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Graduation setting updated successfully.',
            'data' => $setting
        ]);
    }

    /**
     * @param int|string $year
     */
    public function destroy($year): JsonResponse
    {
        $setting = GraduationSetting::where('graduation_year', $year)->firstOrFail();
        $setting->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Graduation setting deleted successfully.'
        ]);
    }
}
