<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('selection_period_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('selection_period_id')->constrained('selection_periods')->cascadeOnDelete();
            $table->foreignId('criteria_id')->constrained('criteria')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['selection_period_id', 'criteria_id']);
        });

        // Data lama: setiap periode yang sudah ada memuat semua kriteria aktif (perilaku sebelum pivot).
        if (! Schema::hasTable('criteria') || ! Schema::hasTable('selection_periods')) {
            return;
        }

        $periodIds = DB::table('selection_periods')->pluck('id');
        $criteriaRows = DB::table('criteria')->where('is_active', true)->orderBy('code')->get(['id']);

        foreach ($periodIds as $pid) {
            $ord = 0;
            foreach ($criteriaRows as $row) {
                DB::table('selection_period_criteria')->insert([
                    'selection_period_id' => $pid,
                    'criteria_id' => $row->id,
                    'sort_order' => $ord++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('selection_period_criteria');
    }
};
