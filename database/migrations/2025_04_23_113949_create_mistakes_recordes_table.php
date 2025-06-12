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
        Schema::create('mistakes_recordes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mistake_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recitation_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('sabr_id')->nullable()->constrained('sabrs')->cascadeOnDelete();
            $table->enum('type', ['recitation', 'sabr']);
            $table->integer('page_number');
            $table->integer('line_number');
            $table->integer('word_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mistakes_recordes');
    }
};
