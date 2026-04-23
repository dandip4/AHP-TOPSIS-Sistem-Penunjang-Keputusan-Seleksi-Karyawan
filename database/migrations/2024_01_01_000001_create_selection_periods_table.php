<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('selection_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'open', 'closed', 'completed'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('selection_periods');
    }
};
