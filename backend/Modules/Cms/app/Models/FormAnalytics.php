<?php

namespace Modules\Cms\Models;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $form_id
 * @property \Illuminate\Support\Carbon $date
 * @property int $views
 * @property int $starts
 * @property int $submissions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Form $form
 */
class FormAnalytics extends Model
{
    use ScopedByWorkspace;
    protected $table = 'form_analytics';

    protected $fillable = [
        'form_id',
        'date',
        'views',
        'starts',
        'submissions',
    ];

    protected $casts = [
        'date' => 'date',
        'views' => 'integer',
        'starts' => 'integer',
        'submissions' => 'integer',
    ];

    /**
     * @return BelongsTo<Form, $this>
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
