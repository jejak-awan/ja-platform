<?php

namespace Modules\School\Models\Operations;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property int $library_book_id
 * @property int $borrower_id
 * @property string $borrower_type
 * @property Carbon $borrow_date
 * @property Carbon $due_date
 * @property Carbon|null $return_date
 * @property string|null $fine_amount
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read LibraryBook $book
 * @property-read MorphTo<Model, $this> $borrower
 */
class LibraryCirculation extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sch_ops_library_circulations';

    /** @use HasFactory<Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $fillable = [
        'library_book_id',
        'borrower_id',
        'borrower_type',
        'borrow_date',
        'due_date',
        'return_date',
        'fine_amount',
        'status',
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'fine_amount' => 'decimal:2',
    ];

    /**
     * @return BelongsTo<LibraryBook, $this>
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(LibraryBook::class, 'library_book_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function borrower(): MorphTo
    {
        return $this->morphTo();
    }
}
