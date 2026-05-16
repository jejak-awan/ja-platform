<?php

namespace Modules\School\Models\Admission;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property int $id
 * @property string $hash
 * @property string $document_type
 * @property int $document_id
 * @property array<string, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class DocumentVerification extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use ScopedByWorkspace;
    protected $table = 'sch_adm_verifications';

    protected $fillable = [
        'hash',
        'document_type',
        'document_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function getVerificationUrl(): string
    {
        return url("/verify/{$this->hash}");
    }
}
