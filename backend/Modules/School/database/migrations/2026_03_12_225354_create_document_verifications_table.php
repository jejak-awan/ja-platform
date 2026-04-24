<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_verifications', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('hash')->unique(); // Unique hash for verification
            $blueprint->string('document_type'); // id_card, skl, receipt, etc.
            $blueprint->unsignedBigInteger('document_id'); // ID of the model (student_id, transaction_id, etc.)
            $blueprint->json('metadata')->nullable(); // Additional data like valid_until, etc.
            $blueprint->timestamps();

            $blueprint->index(['document_type', 'document_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_verifications');
    }
};
