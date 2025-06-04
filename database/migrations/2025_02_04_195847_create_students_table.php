<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_phone')->nullable();
            $table->string('father_phone')->nullable();
            $table->string('location')->nullable();
            $table->date('birth')->nullable();
            $table->string('class')->nullable();
            $table->string('school')->nullable();
            $table->string('father_name')->nullable();
            $table->string('father_job')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_job')->nullable();
            $table->string('qr_token');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('circle_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('level_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('cashed_points')->default(0.00);
            $table->timestamps();
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
