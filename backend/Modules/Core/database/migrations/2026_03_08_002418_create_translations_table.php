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
        Schema::create('core_translations', function (Blueprint $table) {
            $table->id();
            $table->morphs('translatable');
            $table->string('language_code', 10)->index();
            $table->string('field');
            $table->text('value');
            $table->timestamps();

            $table->unique(['translatable_type', 'translatable_id', 'language_code', 'field'], 'translation_unique');
            $table->foreign('language_code')->references('code')->on('core_languages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core_translations');
    }
};
