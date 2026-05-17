<?php

namespace Modules\Forms\Models;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\System\Models\User;


/**
 * @property string $id
 * @property string|null $workspace_id
 * @property string|null $author_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $success_message
 * @property string|null $redirect_url
 * @property array<string, mixed>|null $settings
 * @property bool $is_active
 * @property int $submission_count
 * @property int $view_count
 * @property int $start_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Modules\System\Models\User|null $author
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Forms\Models\FormField> $fields
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Forms\Models\FormSubmission> $submissions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Forms\Models\FormAnalytics> $analytics
 */
class Form extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    /** @use HasFactory<\Modules\Forms\Database\Factories\FormFactory> */
    use HasFactory, SoftDeletes, ScopedByWorkspace;

    protected $table = 'frm_forms';

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\Forms\Database\Factories\FormFactory
    {
        return \Modules\Forms\Database\Factories\FormFactory::new();
    }

    protected $fillable = [
        'workspace_id',
        'author_id',
        'name',
        'slug',
        'description',
        'success_message',
        'redirect_url',
        'settings',
        'is_active',
        'submission_count',
        'view_count',
        'start_count',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'submission_count' => 'integer',
        'view_count' => 'integer',
        'start_count' => 'integer',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return HasMany<FormField, $this>
     */
    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\Forms\Models\FormSubmission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class)->latest();
    }

    /**
     * @return HasMany<FormAnalytics, $this>
     */
    public function analytics(): HasMany
    {
        return $this->hasMany(FormAnalytics::class);
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
        $this->updateDailyStats('views');
    }

    public function incrementStartCount(): void
    {
        $this->increment('start_count');
        $this->updateDailyStats('starts');
    }

    public function incrementSubmissionCount(): void
    {
        $this->increment('submission_count');
        $this->updateDailyStats('submissions');
    }

    /**
     * @param  "views"|"starts"|"submissions"  $field
     */
    protected function updateDailyStats(string $field): void
    {
        $analytics = $this->analytics()->firstOrCreate(
            ['date' => now()->toDateString()],
            ['views' => 0, 'starts' => 0, 'submissions' => 0]
        );

        $analytics->increment($field);
    }

    public function getUnreadSubmissionsCount(): int
    {
        return $this->submissions()->where('status', 'new')->count();
    }
}
