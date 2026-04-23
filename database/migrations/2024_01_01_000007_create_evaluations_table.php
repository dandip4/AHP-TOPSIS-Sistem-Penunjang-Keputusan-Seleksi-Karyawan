<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('selection_periods')->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->foreignId('criteria_id')->constrained('criteria')->cascadeOnDelete();
            $table->decimal('score', 10, 4);
            $table->timestamps();

            $table->unique(['period_id', 'applicant_id', 'criteria_id'], 'eval_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
