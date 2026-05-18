<?php

namespace Modules\School\Models\Institution;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\School\Database\Factories\SchoolFactory;
use Modules\School\Models\Academic\AcademicYear;
use Modules\School\Models\Academic\StudyGroup;
use Modules\School\Models\Academic\Subject;
use Modules\School\Models\HR\Staff;
use Modules\School\Models\Logistics\LandAsset;
use Modules\School\Models\Operations\GuestLog;
use Modules\School\Models\Operations\LibraryBook;
use Modules\School\Models\Operations\UksVisit;
use Modules\School\Models\Student\Student;

// use Modules\School\Database\Factories\SchoolFactory;

/**
 * @property string $id
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, SchoolUnit> $levels
 * @property-read Collection<int, AcademicYear> $academicYears
 * @property-read AcademicYear|null $activeAcademicYear
 * @property-read Collection<int, Student> $students
 * @property-read Collection<int, Staff> $staff
 */
class School extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /** @use HasFactory<SchoolFactory> */
    use HasFactory, SoftDeletes;

    protected static function newFactory(): SchoolFactory
    {
        return SchoolFactory::new();
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
     * @return HasMany<SchoolUnit, $this>
     */
    public function levels(): HasMany
    {
        return $this->hasMany(SchoolUnit::class);
    }

    /**
     * @return HasMany<AcademicYear, $this>
     */
    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class);
    }

    /**
     * @return HasOne<AcademicYear, $this>
     */
    public function activeAcademicYear(): HasOne
    {
        return $this->hasOne(AcademicYear::class)->where('is_active', true);
    }

    /**
     * @return HasMany<Student, $this>
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * @return HasMany<Staff, $this>
     */
    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    /**
     * @return HasMany<LandAsset, $this>
     */
    public function landAssets(): HasMany
    {
        return $this->hasMany(LandAsset::class);
    }

    /**
     * @return HasMany<Subject, $this>
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * @return HasMany<StudyGroup, $this>
     */
    public function studyGroups(): HasMany
    {
        return $this->hasMany(StudyGroup::class);
    }

    /**
     * @return HasMany<LibraryBook, $this>
     */
    public function libraryBooks(): HasMany
    {
        return $this->hasMany(LibraryBook::class);
    }

    /**
     * @return HasMany<UksVisit, $this>
     */
    public function uksVisits(): HasMany
    {
        return $this->hasMany(UksVisit::class);
    }

    /**
     * @return HasMany<GuestLog, $this>
     */
    public function guestLogs(): HasMany
    {
        return $this->hasMany(GuestLog::class);
    }
}
