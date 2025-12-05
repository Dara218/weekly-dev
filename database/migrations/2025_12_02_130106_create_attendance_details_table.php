<?php

use App\Enum\AttendanceDetailStatus;
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
        Schema::create('attendance_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('attendance_id')->constrained('attendance_records', 'id');
            $table->foreignId('student_id')->constrained();
            $table->enum('status', AttendanceDetailStatus::list());
            $table->text('remark')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_details');
    }
};
