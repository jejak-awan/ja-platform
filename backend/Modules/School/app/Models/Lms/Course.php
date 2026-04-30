<?php

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\User;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Student\Student;

class Course extends Model
{
    use SoftDeletes;

    protected $table = 'sch_lms_courses';

    protected $fillable = [
        'school_id',
        'title',
        'slug',
        'summary',
        'description',
        'image_path',
        'video_url',
        'base_price',
        'discount_price',
        'status',
        'level',
        'metadata',
        'author_id',
    ];

    protected $casts = [
        'metadata' => 'json',
        'base_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'sch_lms_enrollments', 'course_id', 'student_id');
    }
}
