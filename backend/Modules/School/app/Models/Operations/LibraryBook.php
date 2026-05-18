<?php

namespace Modules\School\Models\Operations;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\School\Models\Institution\School;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $school_id
 * @property string|null $isbn
 * @property string $title
 * @property string|null $author
 * @property string|null $publisher
 * @property string|null $publish_year
 * @property int $quantity
 * @property string|null $location
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read School $school
 */
class LibraryBook extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sch_ops_library_books';

    /** @use HasFactory<Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $fillable = [
        'school_id',
        'isbn',
        'title',
        'author',
        'publisher',
        'publish_year',
        'quantity',
        'location',
    ];

    /**
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
