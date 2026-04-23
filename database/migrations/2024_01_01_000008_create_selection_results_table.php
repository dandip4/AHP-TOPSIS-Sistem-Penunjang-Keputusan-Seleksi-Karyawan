<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('selection_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('selection_periods')->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->decimal('preference_value', 10, 6);
            $table->decimal('positive_distance', 10, 6)->nullable();
            $table->decimal('negative_distance', 10, 6)->nullable();
            $table->integer('rank');
            $table->enum('status', ['lulus', 'tidak_lulus'])->default('tidak_lulus');
            $table->timestamps();

            $table->unique(['period_id', 'applicant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('selection_results');
    }
};
