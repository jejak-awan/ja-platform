<?php

namespace Modules\Library\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\System\Models\User;
use Modules\System\Traits\ScopedByWorkspace;

class CustomField extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use HasFactory, SoftDeletes, ScopedByWorkspace;

    protected $table = 'lib_fields';

    protected $fillable = [
        'name',
        'key',
        'type',
        'options',
        'rules',
        'default_value',
        'placeholder',
        'help_text',
        'is_required',
        'is_filterable',
        'sort_order',
        'workspace_id',
        'author_id',
    ];

    protected $casts = [
        'options' => 'array',
        'rules' => 'array',
        'is_required' => 'boolean',
        'is_filterable' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsToMany<FieldGroup, $this>
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(FieldGroup::class, 'lib_field_group_pivot', 'field_id', 'group_id')
            ->withPivot('sort_order')
            ->orderBy('lib_field_group_pivot.sort_order');
    }
}
