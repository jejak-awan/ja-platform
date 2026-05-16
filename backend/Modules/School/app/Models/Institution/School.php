<?php

namespace Modules\School\Models\Institution;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

// use Modules\School\Database\Factories\SchoolFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property bool $is_multi_unit
 * @property bool $is_multi_branch
 * @property string|null $npsn
 * @property string|null $nss
 * @property string|null $nds
 * @property string|null $status_kepemilikan
 * @property string|null $accreditation
 * @property string|null $kurikulum
 * @property string|null $sk_pendirian
 * @property string|null $tgl_sk_pendirian
 * @property string|null $sk_operasional
 * @property string|null $tgl_sk_operasional
 * @property string|null $address
 * @property string|null $rt
 * @property string|null $rw
 * @property string|null $dusun
 * @property string|null $desa_kelurahan
 * @property string|null $kecamatan
 * @property string|null $kabupaten_kota
 * @property string|null $provinsi
 * @property string|null $kode_pos
 * @property string|null $lat
 * @property string|null $long
 * @property string|null $phone
 * @property string|null $fax
 * @property string|null $email
 * @property string|null $website
 * @property string|null $npwp
 * @property string|null $bank_name
 * @property string|null $bank_account_number
 * @property string|null $bank_account_holder
 * @property string|null $foundation_name
 * @property string|null $akta_pendirian_yayasan
 * @property string|null $tgl_akta_pendirian_yayasan
 * @property string|null $sk_kemenkumham
 * @property string|null $tgl_sk_kemenkumham
 * @property string|null $nib
 * @property string|null $principal_name
 * @property string|null $vision
 * @property string|null $mission
 * @property string|null $goals
 * @property string|null $history
 * @property string|null $org_structure_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SchoolUnit> $levels
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\School\Models\Academic\AcademicYear> $academicYears
 * @property-read \Modules\School\Models\Academic\AcademicYear|null $activeAcademicYear
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\School\Models\Student\Student> $students
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\School\Models\HR\Staff> $staff
 */
class School extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    /** @use HasFactory<\Modules\School\Database\Factories\SchoolFactory> */
    use HasFactory, SoftDeletes;

    protected static function newFactory(): \Modules\School\Database\Factories\SchoolFactory
    {
        return \Modules\School\Database\Factories\SchoolFactory::new();
    }

    protected $table = 'sch_ins_schools';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'type',
        'is_multi_unit',
        'is_multi_branch',
        'npsn',
        'nss',
        'nds',
        'status_kepemilikan',
        'accreditation',
        'kurikulum',
        'sk_pendirian',
        'tgl_sk_pendirian',
        'sk_operasional',
        'tgl_sk_operasional',
        'address',
        'rt',
        'rw',
        'dusun',
        'desa_kelurahan',
        'kecamatan',
        'kabupaten_kota',
        'provinsi',
        'kode_pos',
        'lat',
        'long',
        'phone',
        'fax',
        'email',
        'website',
        'npwp',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'foundation_name',
        'akta_pendirian_yayasan',
        'tgl_akta_pendirian_yayasan',
        'sk_kemenkumham',
        'tgl_sk_kemenkumham',
        'nib',
        'principal_name',
        'vision',
        'mission',
        'goals',
        'history',
        'org_structure_path',
    ];

    protected $casts = [
        'is_multi_unit' => 'boolean',
        'is_multi_branch' => 'boolean',
        'tgl_sk_pendirian' => 'date',
        'tgl_sk_operasional' => 'date',
        'tgl_akta_pendirian_yayasan' => 'date',
        'tgl_sk_kemenkumham' => 'date',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<SchoolUnit, $this>
     */
    public function levels(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SchoolUnit::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\School\Models\Academic\AcademicYear, $this>
     */
    public function academicYears(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\School\Models\Academic\AcademicYear::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\Modules\School\Models\Academic\AcademicYear, $this>
     */
    public function activeAcademicYear(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\Modules\School\Models\Academic\AcademicYear::class)->where('is_active', true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\School\Models\Student\Student, $this>
     */
    public function students(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\School\Models\Student\Student::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\School\Models\HR\Staff, $this>
     */
    public function staff(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\School\Models\HR\Staff::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\School\Models\Logistics\LandAsset, $this>
     */
    public function landAssets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\School\Models\Logistics\LandAsset::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\School\Models\Academic\Subject, $this>
     */
    public function subjects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\School\Models\Academic\Subject::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\School\Models\Academic\StudyGroup, $this>
     */
    public function studyGroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\School\Models\Academic\StudyGroup::class);
    }



    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\School\Models\Operations\LibraryBook, $this>
     */
    public function libraryBooks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\School\Models\Operations\LibraryBook::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\School\Models\Operations\UksVisit, $this>
     */
    public function uksVisits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\School\Models\Operations\UksVisit::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\School\Models\Operations\GuestLog, $this>
     */
    public function guestLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\School\Models\Operations\GuestLog::class);
    }
}
