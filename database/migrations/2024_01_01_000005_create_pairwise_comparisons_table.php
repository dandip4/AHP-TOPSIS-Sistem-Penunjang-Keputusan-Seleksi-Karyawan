<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pairwise_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('selection_periods')->cascadeOnDelete();
            $table->foreignId('criteria_row_id')->constrained('criteria')->cascadeOnDelete();
            $table->foreignId('criteria_col_id')->constrained('criteria')->cascadeOnDelete();
            $table->decimal('value', 10, 4);
            $table->timestamps();

            $table->unique(['period_id', 'criteria_row_id', 'criteria_col_id'], 'pc_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pairwise_comparisons');
    }
};
