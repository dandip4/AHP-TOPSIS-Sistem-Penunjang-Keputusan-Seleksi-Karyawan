<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('selection_periods')->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->enum('gender', ['L', 'P']);
            $table->date('birth_date')->nullable();
            $table->string('education')->nullable();
            $table->string('major')->nullable();
            $table->decimal('gpa', 3, 2)->nullable();
            $table->integer('age')->nullable();
            $table->text('address')->nullable();
            $table->string('photo')->nullable();
            $table->string('cv')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};
