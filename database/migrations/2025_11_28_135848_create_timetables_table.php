<?php

use App\Enum\DayOfWeek;
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
        Schema::create('timetables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('class_id')->constrained();
            $table->foreignId('section_id')->constrained();
            $table->enum('day_of_week', DayOfWeek::list());
            $table->integer('period')->comment('1-10');
            $table->foreignId('subject_id')->constrained();
            $table->foreignId('teacher_id')->constrained();
            $table->string('room', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetables');
    }
};
