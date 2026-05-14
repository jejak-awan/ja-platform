<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $media_id
 * @property string $model_type
 * @property int $model_id
 * @property string|null $field_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Core\Models\Media $media
 * @property-read \Illuminate\Database\Eloquent\Model $model
 */
class MediaUsage extends Model
{
    protected $table = 'core_media_usages';



    protected $fillable = [
        'media_id',
        'model_type',
        'model_id',
        'field_name',
    ];

    /**
     * @return BelongsTo<Media, $this>
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /**
     * @return MorphTo<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Helper method to track media usage
     *
     * @param  int  $mediaId
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string|null  $fieldName
     */
    public static function track($mediaId, $model, $fieldName = null): self
    {
        // Remove old usage for this field if exists
        if ($fieldName) {
            $modelId = $model->getAttribute('id');
            $id = is_int($modelId) ? $modelId : (is_string($modelId) ? (int) $modelId : 0);
            self::where('model_type', $model->getMorphClass())
                ->where('model_id', $id)
                ->where('field_name', $fieldName)
                ->delete();
        }

        $modelId = $model->getAttribute('id');
        $id = is_int($modelId) ? $modelId : (is_string($modelId) ? (int) $modelId : 0);

        return self::create([
            'media_id' => $mediaId,
            'model_type' => $model->getMorphClass(),
            'model_id' => $id,
            'field_name' => $fieldName,
        ]);
    }

    /**
     * Helper method to remove media usage
     *
     * @param  int|null  $mediaId
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @param  string|null  $fieldName
     */
    public static function untrack($mediaId = null, $model = null, $fieldName = null): int
    {
        $query = self::query();

        if ($mediaId) {
            $query->where('media_id', $mediaId);
        }

        if ($model) {
            $modelId = $model->getAttribute('id');
            $id = is_int($modelId) ? $modelId : (is_string($modelId) ? (int) $modelId : 0);
            $query->where('model_type', $model->getMorphClass())
                ->where('model_id', $id);
        }

        if ($fieldName) {
            $query->where('field_name', $fieldName);
        }

        $deleted = $query->delete();

        return is_int($deleted) ? $deleted : (is_numeric($deleted) ? (int) $deleted : 0);
    }
}
