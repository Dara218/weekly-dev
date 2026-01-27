<?php

use App\Enum\TeacherClassAssignmentStatus;
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
        Schema::create('teacher_class_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained();
            $table->foreignId('class_id')->constrained();
            $table->foreignId('section_id')->constrained();
            $table->foreignId('academic_year_id')->constrained();
            $table->enum('status', TeacherClassAssignmentStatus::list());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_class_assignments');
    }
};
