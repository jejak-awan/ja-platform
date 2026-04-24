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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['negeri', 'swasta'])->default('swasta');
            $table->string('npsn', 8)->unique()->nullable();
            $table->string('nss', 20)->nullable();
            $table->string('nds', 20)->nullable();
            $table->string('status_kepemilikan')->nullable();
            $table->string('accreditation')->nullable(); // A, B, C, TT
            $table->string('kurikulum')->nullable();

            // Legal/Permits
            $table->string('sk_pendirian')->nullable();
            $table->date('tgl_sk_pendirian')->nullable();
            $table->string('sk_operasional')->nullable();
            $table->date('tgl_sk_operasional')->nullable();

            // Detailed Location
            $table->text('address')->nullable();
            $table->string('rt', 3)->nullable();
            $table->string('rw', 3)->nullable();
            $table->string('dusun')->nullable();
            $table->string('desa_kelurahan')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kabupaten_kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kode_pos', 5)->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('long', 11, 8)->nullable();

            // Contact & Web
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            // Financial/Tax
            $table->string('npwp', 20)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_holder')->nullable();

            // Institutional Profile
            $table->string('foundation_name')->nullable();
            $table->string('principal_name')->nullable();
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->text('goals')->nullable();
            $table->text('history')->nullable();
            $table->string('org_structure_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
