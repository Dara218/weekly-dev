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
        Schema::create('teachers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained();
            $table->string('employee_code', 50)->comment('TCH-2025-00045 (Teacher - Year Hired - Sequence)');
            $table->string('phone', 20);
            $table->string('specialization', 255);
            $table->integer('experience_years');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
