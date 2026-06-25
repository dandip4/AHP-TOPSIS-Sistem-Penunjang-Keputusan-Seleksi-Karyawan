<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('selection_period_evaluators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('selection_period_id');
            $table->unsignedBigInteger('evaluator_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('selection_period_id')
                ->references('id')
                ->on('selection_periods')
                ->onDelete('cascade');

            $table->foreign('evaluator_id')
                ->references('id')
                ->on('evaluators')
                ->onDelete('cascade');

            // Unique constraint: satu evaluator hanya bisa ikut satu kali di satu periode
            $table->unique(['selection_period_id', 'evaluator_id'], 'period_evaluator_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('selection_period_evaluators');
    }
};
