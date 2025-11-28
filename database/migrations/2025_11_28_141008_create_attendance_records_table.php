<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Todo: Continue migration; Table: attendance_details.

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id');
            $table->date('date');
            $table->foreignId('marked_by')->constrained('teachers', 'id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
