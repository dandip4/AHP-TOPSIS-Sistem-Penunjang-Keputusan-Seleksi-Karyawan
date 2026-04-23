<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criteria_id')->constrained('criteria')->cascadeOnDelete();
            $table->string('name');
            $table->integer('value');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_criteria');
    }
};
