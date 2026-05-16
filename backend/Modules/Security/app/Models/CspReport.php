<?php

namespace Modules\Security\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CspReport extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'sec_csp_reports';


    protected $fillable = [
        'document_uri',
        'violated_directive',
        'blocked_uri',
        'source_file',
        'line_number',
        'user_agent',
        'ip_address',
        'raw_report',
        'status',
    ];

    protected $casts = [
        'raw_report' => 'array',
        'line_number' => 'integer',
    ];

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<$this>  $query
     * @return \Illuminate\Database\Eloquent\Builder<$this>
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<$this>  $query
     * @param  string  $directive
     * @return \Illuminate\Database\Eloquent\Builder<$this>
     */
    public function scopeByDirective($query, $directive)
    {
        return $query->where('violated_directive', $directive);
    }
}
