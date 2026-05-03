<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sch_ins_schools', function (Blueprint $table) {
            $table->string('akta_pendirian_yayasan')->nullable()->after('foundation_name')
                ->comment('Nomor Akta Pendirian Yayasan');
            $table->date('tgl_akta_pendirian_yayasan')->nullable()->after('akta_pendirian_yayasan')
                ->comment('Tanggal Akta Pendirian Yayasan');
            $table->string('sk_kemenkumham')->nullable()->after('tgl_akta_pendirian_yayasan')
                ->comment('Nomor SK Pengesahan Kemenkumham');
            $table->date('tgl_sk_kemenkumham')->nullable()->after('sk_kemenkumham')
                ->comment('Tanggal SK Kemenkumham');
            $table->string('nib')->nullable()->after('tgl_sk_kemenkumham')
                ->comment('Nomor Induk Berusaha (OSS)');
        });
    }

    public function down(): void
    {
        Schema::table('sch_ins_schools', function (Blueprint $table) {
            $table->dropColumn([
                'akta_pendirian_yayasan',
                'tgl_akta_pendirian_yayasan',
                'sk_kemenkumham',
                'tgl_sk_kemenkumham',
                'nib',
            ]);
        });
    }
};
