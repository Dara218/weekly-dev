<?php

use App\Enum\Gender;
use App\Enum\StudentStatus;
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
        Schema::create('students', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('parent_id')->constrained();
            $table->string('admission_no', 50)->comment('201911143');
            $table->foreignId('class_id')->constrained();
            $table->foreignId('section_id')->constrained();
            $table->enum('gender', Gender::list());
            $table->date('dob')->comment('Date of birth: 2010-05-15');
            $table->text('address');
            $table->enum('student_status', StudentStatus::list());
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
