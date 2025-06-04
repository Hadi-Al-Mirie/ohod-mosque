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
        Schema::create('result_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['recitation', 'sabr']);
            $table->string('name');
            $table->integer('min_res');
            $table->integer('max_res');
            $table->integer('points');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('result_settings');
    }
};
