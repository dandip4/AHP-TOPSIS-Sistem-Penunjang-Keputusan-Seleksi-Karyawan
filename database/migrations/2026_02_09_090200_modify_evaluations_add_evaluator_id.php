<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::table('evaluations', function (Blueprint $table): void {
                $table->index('period_id', 'evaluations_period_id_index');
                $table->index('applicant_id', 'evaluations_applicant_id_index');
                $table->index('criteria_id', 'evaluations_criteria_id_index');
                $table->dropUnique('eval_unique');
            });

            $now = now();
            $defaultEvaluatorId = DB::table('evaluators')->insertGetId([
                'code' => 'default',
                'name' => 'Evaluator Default (migrasi)',
                'role_label' => null,
                'user_id' => null,
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            Schema::table('evaluations', function (Blueprint $table) use ($defaultEvaluatorId): void {
                $table->unsignedBigInteger('evaluator_id')->default($defaultEvaluatorId)->after('criteria_id');
            });

            Schema::table('evaluations', function (Blueprint $table): void {
                $table->foreign('evaluator_id')
                    ->references('id')
                    ->on('evaluators')
                    ->cascadeOnDelete();
                $table->unique(
                    ['period_id', 'applicant_id', 'criteria_id', 'evaluator_id'],
                    'eval_period_applicant_criteria_eval_unique'
                );
            });
        });
    }

    public function down(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::table('evaluations', function (Blueprint $table): void {
                $table->dropForeign(['evaluator_id']);
                $table->dropUnique('eval_period_applicant_criteria_eval_unique');
                $table->dropColumn('evaluator_id');
            });

            Schema::table('evaluations', function (Blueprint $table): void {
                $table->unique(['period_id', 'applicant_id', 'criteria_id'], 'eval_unique');
            });

            Schema::table('evaluations', function (Blueprint $table): void {
                $table->dropIndex('evaluations_period_id_index');
                $table->dropIndex('evaluations_applicant_id_index');
                $table->dropIndex('evaluations_criteria_id_index');
            });
        });

        DB::table('evaluators')->where('code', 'default')->delete();
    }
};
