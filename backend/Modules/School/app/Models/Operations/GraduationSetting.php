<?php

namespace Modules\School\Models\Operations;

use Illuminate\Database\Eloquent\Model;

class GraduationSetting extends Model
{
    protected $table = 'sch_grad_settings';

    protected $fillable = [
        'graduation_year',
        'is_open',
        'announcement_date',
        'subjects',
        'config',
    ];

    protected $casts = [
        'graduation_year' => 'integer',
        'is_open' => 'boolean',
        'announcement_date' => 'datetime',
        'subjects' => 'json',
        'config' => 'json',
    ];
}
