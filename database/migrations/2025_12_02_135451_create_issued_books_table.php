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
        Schema::create('issued_books', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('book_id')->constrained();
            $table->foreignId('issued_to_student_id')->constrained('students', 'id');
            $table->date('issue_date');
            $table->date('return_date');
            $table->timestamp('return_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issued_books');
    }
};
