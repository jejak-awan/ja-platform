<?php

namespace Modules\Cms\Models;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\System\Models\User;

/**
 * @property int $id
 * @property int $form_id
 * @property int|null $user_id
 * @property array<string, mixed> $data
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read int|null $count
 * @property-read string|null $label
 * @property-read \Modules\Cms\Models\Form $form
 * @property-read \Modules\System\Models\User|null $user
 */
class FormSubmission extends Model
{
    /** @use HasFactory<\Modules\Cms\Database\Factories\FormSubmissionFactory> */
    use HasFactory, SoftDeletes, ScopedByWorkspace;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\Cms\Database\Factories\FormSubmissionFactory
    {
        return \Modules\Cms\Database\Factories\FormSubmissionFactory::new();
    }

    protected $fillable = [
        'form_id',
        'user_id',
        'data',
        'ip_address',
        'user_agent',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * @return BelongsTo<Form, $this>
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        $this->update(['status' => 'read']);
    }

    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    public function getFieldValue(string $fieldName): mixed
    {
        return $this->data[$fieldName] ?? null;
    }
}
