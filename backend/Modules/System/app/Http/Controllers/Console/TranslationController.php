<?php

namespace Modules\System\Http\Controllers\Console;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\System\Models\Translation;

class TranslationController extends BaseApiController
{
    /**
     * Get translations for a specific entity.
     */
    public function getTranslations(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'translatable_type' => 'required|string',
            'translatable_id' => 'required|integer',
        ]);

        $translations = Translation::where('translatable_type', $request->input('translatable_type'))
            ->where('translatable_id', $request->input('translatable_id'))
            ->get();

        return $this->success($translations, 'Translations retrieved successfully');
    }

    /**
     * Set a translation for a specific entity.
     */
    public function setTranslation(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'translatable_type' => 'required|string',
            'translatable_id' => 'required|integer',
            'language_code' => ['required', 'string', 'max:10', Rule::exists('languages', 'code')],
            'field' => 'required|string|max:255',
            'value' => 'required|string',
        ]);

        $translation = Translation::updateOrCreate(
            [
                'translatable_type' => $request->input('translatable_type'),
                'translatable_id' => $request->input('translatable_id'),
                'language_code' => $request->input('language_code'),
                'field' => $request->input('field'),
            ],
            [
                'value' => $request->input('value'),
            ]
        );

        return $this->success($translation, 'Translation saved successfully');
    }
}
