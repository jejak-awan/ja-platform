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
        Schema::create('library_circulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_book_id')->constrained()->onDelete('cascade');
            $table->morphs('borrower'); // student or staff
            $table->date('borrow_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->decimal('fine_amount', 10, 2)->default(0);
            $table->string('status')->default('Borrowed'); // Borrowed, Returned, Overdue, Lost
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_circulations');
    }
};
