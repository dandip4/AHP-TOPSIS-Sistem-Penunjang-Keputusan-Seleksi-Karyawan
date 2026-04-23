<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('criteria_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('selection_periods')->cascadeOnDelete();
            $table->foreignId('criteria_id')->constrained('criteria')->cascadeOnDelete();
            $table->decimal('weight', 10, 6);
            $table->timestamps();

            $table->unique(['period_id', 'criteria_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('criteria_weights');
    }
};
