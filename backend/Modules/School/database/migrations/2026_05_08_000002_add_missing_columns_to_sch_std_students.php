<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sch_std_students', function (Blueprint $table): void {
            if (! Schema::hasColumn('sch_std_students', 'department_id')) {
                $table->uuid('department_id')
                    ->nullable()
                    
                    ->nullOnDelete()
                    ->after('workspace_id');
            }

            foreach ([
                'nik' => 16,
                'rt' => 3,
                'rw' => 3,
                'father_nik' => 16,
                'mother_nik' => 16,
            ] as $column => $len) {
                if (! Schema::hasColumn('sch_std_students', $column)) {
                    $table->string($column, $len)->nullable();
                }
            }

            foreach (['father_occupation', 'mother_occupation'] as $column) {
                if (! Schema::hasColumn('sch_std_students', $column)) {
                    $table->string($column)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('sch_std_students', function (Blueprint $table): void {
            if (Schema::hasColumn('sch_std_students', 'department_id')) {
                $table->dropConstrainedForeignId('department_id');
            }

            foreach (['nik', 'rt', 'rw', 'father_nik', 'father_occupation', 'mother_nik', 'mother_occupation'] as $column) {
                if (Schema::hasColumn('sch_std_students', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

