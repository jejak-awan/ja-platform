<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (! Schema::hasColumn('schools', 'type')) {
                $table->enum('type', ['negeri', 'swasta'])->default('swasta');
            }
            if (! Schema::hasColumn('schools', 'npsn')) {
                $table->string('npsn', 8)->unique()->nullable();
            }
            if (! Schema::hasColumn('schools', 'nss')) {
                $table->string('nss', 20)->nullable();
            }
            if (! Schema::hasColumn('schools', 'nds')) {
                $table->string('nds', 20)->nullable();
            }
            if (! Schema::hasColumn('schools', 'status_kepemilikan')) {
                $table->string('status_kepemilikan')->nullable();
            }
            if (! Schema::hasColumn('schools', 'accreditation')) {
                $table->string('accreditation')->nullable();
            }
            if (! Schema::hasColumn('schools', 'kurikulum')) {
                $table->string('kurikulum')->nullable();
            }
            if (! Schema::hasColumn('schools', 'sk_pendirian')) {
                $table->string('sk_pendirian')->nullable();
            }
            if (! Schema::hasColumn('schools', 'tgl_sk_pendirian')) {
                $table->date('tgl_sk_pendirian')->nullable();
            }
            if (! Schema::hasColumn('schools', 'sk_operasional')) {
                $table->string('sk_operasional')->nullable();
            }
            if (! Schema::hasColumn('schools', 'tgl_sk_operasional')) {
                $table->date('tgl_sk_operasional')->nullable();
            }
            if (! Schema::hasColumn('schools', 'rt')) {
                $table->string('rt', 3)->nullable();
            }
            if (! Schema::hasColumn('schools', 'rw')) {
                $table->string('rw', 3)->nullable();
            }
            if (! Schema::hasColumn('schools', 'dusun')) {
                $table->string('dusun')->nullable();
            }
            if (! Schema::hasColumn('schools', 'desa_kelurahan')) {
                $table->string('desa_kelurahan')->nullable();
            }
            if (! Schema::hasColumn('schools', 'kecamatan')) {
                $table->string('kecamatan')->nullable();
            }
            if (! Schema::hasColumn('schools', 'kabupaten_kota')) {
                $table->string('kabupaten_kota')->nullable();
            }
            if (! Schema::hasColumn('schools', 'provinsi')) {
                $table->string('provinsi')->nullable();
            }
            if (! Schema::hasColumn('schools', 'kode_pos')) {
                $table->string('kode_pos', 5)->nullable();
            }
            if (! Schema::hasColumn('schools', 'lat')) {
                $table->decimal('lat', 10, 8)->nullable();
            }
            if (! Schema::hasColumn('schools', 'long')) {
                $table->decimal('long', 11, 8)->nullable();
            }
            if (! Schema::hasColumn('schools', 'fax')) {
                $table->string('fax')->nullable();
            }
            if (! Schema::hasColumn('schools', 'npwp')) {
                $table->string('npwp', 20)->nullable();
            }
            if (! Schema::hasColumn('schools', 'bank_name')) {
                $table->string('bank_name')->nullable();
            }
            if (! Schema::hasColumn('schools', 'bank_account_number')) {
                $table->string('bank_account_number')->nullable();
            }
            if (! Schema::hasColumn('schools', 'bank_account_holder')) {
                $table->string('bank_account_holder')->nullable();
            }
            if (! Schema::hasColumn('schools', 'foundation_name')) {
                $table->string('foundation_name')->nullable();
            }
            if (! Schema::hasColumn('schools', 'principal_name')) {
                $table->string('principal_name')->nullable();
            }
            if (! Schema::hasColumn('schools', 'vision')) {
                $table->text('vision')->nullable();
            }
            if (! Schema::hasColumn('schools', 'mission')) {
                $table->text('mission')->nullable();
            }
            if (! Schema::hasColumn('schools', 'goals')) {
                $table->text('goals')->nullable();
            }
            if (! Schema::hasColumn('schools', 'history')) {
                $table->text('history')->nullable();
            }
            if (! Schema::hasColumn('schools', 'org_structure_path')) {
                $table->string('org_structure_path')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'type', 'npsn', 'nss', 'nds', 'status_kepemilikan', 'accreditation', 'kurikulum',
                'sk_pendirian', 'tgl_sk_pendirian', 'sk_operasional', 'tgl_sk_operasional',
                'rt', 'rw', 'dusun', 'desa_kelurahan', 'kecamatan', 'kabupaten_kota', 'provinsi',
                'kode_pos', 'lat', 'long', 'fax', 'npwp', 'bank_name', 'bank_account_number',
                'bank_account_holder', 'foundation_name', 'principal_name', 'vision', 'mission',
                'goals', 'history', 'org_structure_path',
            ]);
        });
    }
};
