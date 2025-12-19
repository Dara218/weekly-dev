<?php

use App\Enum\{
    UserActiveStatus,
    UserRole,
};
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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('User ID');
            $table->string('name', 255)->comment('Full Name');
            $table->string('email', 255)->unique()->comment('Login Email');
            $table->string('password', 255)->comment('Hashed Password');
            $table->enum('role', UserRole::list())->comment('System Role: Admin, Teacher, Student, Parent');
            $table->string('profile_image', 255)->nullable()->comment('Avatar');
            $table->boolean('is_active')->default(UserActiveStatus::ACTIVE)->comment('Soft Disable');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
