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
        Schema::create('helper_teacher_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('helper_teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // give the unique index a short, custom name
            $table->unique(
                ['helper_teacher_id', 'permission_id'],
                'ht_perm_unique'
            );
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helper_teacher_permissions');
    }
};