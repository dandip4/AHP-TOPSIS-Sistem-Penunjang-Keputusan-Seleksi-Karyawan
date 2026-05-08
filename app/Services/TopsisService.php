<?php

namespace App\Services;

use App\Models\AggregatedEvaluation;
use App\Models\Applicant;
use App\Models\CriteriaWeight;
use App\Models\SelectionPeriod;
use App\Models\SelectionResult;

class TopsisService
{
    public function __construct(
        private readonly GroupDecisionAggregator $groupDecisionAggregator,
    ) {}

    public function getCalculationData(int $periodId): array
    {
        $weights = CriteriaWeight::where('period_id', $periodId)
            ->with('criteria')
            ->get()
            ->keyBy('criteria_id');

        if ($weights->isEmpty()) {
            return ['error' => 'Bobot kriteria belum tersedia untuk periode ini. Atur bobot di data periode (kriteria terpilih) atau lakukan perhitungan AHP.'];
        }

        $applicants = Applicant::where('period_id', $periodId)->get();
        if ($applicants->isEmpty()) {
            return ['error' => 'Tidak ada pelamar pada periode ini.'];
        }

        if (! $this->groupDecisionAggregator->isAggregateMatrixComplete($periodId)) {
            return [
                'error' => 'Matriks agregat KMKK belum siap atau belum lengkap. Gunakan halaman Evaluasi Kelompok (KMKK) untuk menyatukan penilaian semua evaluator, lalu lakukan lagi perhitungan TOPSIS.',
            ];
        }

        $aggregates = AggregatedEvaluation::where('period_id', $periodId)->get()->keyBy(
            fn (AggregatedEvaluation $row): string => $row->applicant_id.'_'.$row->criteria_id
        );

        $criteriaIds = $weights->keys()->toArray();
        $criteriaTypes = [];
        foreach ($weights as $cId => $w) {
            $criteriaTypes[$cId] = $w->criteria->type;
        }

        // Step 1: Build decision matrix (nilai dari agregasi kelompok: rata-rata atau OWA Yager)
        $decisionMatrix = [];
        foreach ($applicants as $applicant) {
            foreach ($criteriaIds as $cId) {
                /** @var AggregatedEvaluation|null $cell */
                $cell = $aggregates->get($applicant->id.'_'.$cId);
                $decisionMatrix[$applicant->id][$cId] = $cell ? (float) $cell->aggregated_score : 0;
            }
        }

        // Step 2: Normalize decision matrix
        $divisors = [];
        foreach ($criteriaIds as $cId) {
            $sumSquares = 0;
            foreach ($applicants as $applicant) {
                $sumSquares += pow($decisionMatrix[$applicant->id][$cId], 2);
            }
            $divisors[$cId] = sqrt($sumSquares);
        }

        $normalizedMatrix = [];
        foreach ($applicants as $applicant) {
            foreach ($criteriaIds as $cId) {
                $normalizedMatrix[$applicant->id][$cId] = $divisors[$cId] > 0
                    ? $decisionMatrix[$applicant->id][$cId] / $divisors[$cId]
                    : 0;
            }
        }

        // Step 3: Weighted normalized matrix
        $weightedMatrix = [];
        foreach ($applicants as $applicant) {
            foreach ($criteriaIds as $cId) {
                $weightedMatrix[$applicant->id][$cId] =
                    $normalizedMatrix[$applicant->id][$cId] * (float) $weights[$cId]->weight;
            }
        }

        // Step 4: Ideal positive (A+) and negative (A-) solutions
        $idealPositive = [];
        $idealNegative = [];
        foreach ($criteriaIds as $cId) {
            $values = [];
            foreach ($applicants as $applicant) {
                $values[] = $weightedMatrix[$applicant->id][$cId];
            }

            if ($criteriaTypes[$cId] === 'benefit') {
                $idealPositive[$cId] = max($values);
                $idealNegative[$cId] = min($values);
            } else {
                $idealPositive[$cId] = min($values);
                $idealNegative[$cId] = max($values);
            }
        }

        // Step 5: Distance to ideal solutions
        $distances = [];
        foreach ($applicants as $applicant) {
            $dPlus = 0;
            $dMinus = 0;
            foreach ($criteriaIds as $cId) {
                $dPlus += pow($weightedMatrix[$applicant->id][$cId] - $idealPositive[$cId], 2);
                $dMinus += pow($weightedMatrix[$applicant->id][$cId] - $idealNegative[$cId], 2);
            }
            $distances[$applicant->id] = [
                'positive' => sqrt($dPlus),
                'negative' => sqrt($dMinus),
            ];
        }

        // Step 6: Preference values
        $preferences = [];
        foreach ($applicants as $applicant) {
            $dPlus = $distances[$applicant->id]['positive'];
            $dMinus = $distances[$applicant->id]['negative'];
            $total = $dPlus + $dMinus;
            $preferences[$applicant->id] = $total > 0 ? $dMinus / $total : 0;
        }

        // Step 7: Ranking
        arsort($preferences);
        $rank = 1;
        $rankings = [];
        foreach ($preferences as $applicantId => $prefValue) {
            $rankings[$applicantId] = $rank++;
        }

        $periodSummary = SelectionPeriod::find($periodId);

        return [
            'decision_matrix' => $decisionMatrix,
            'divisors' => $divisors,
            'normalized_matrix' => $normalizedMatrix,
            'weighted_matrix' => $weightedMatrix,
            'ideal_positive' => $idealPositive,
            'ideal_negative' => $idealNegative,
            'distances' => $distances,
            'preferences' => $preferences,
            'rankings' => $rankings,
            'applicants' => $applicants->keyBy('id'),
            'criteria_ids' => $criteriaIds,
            'weights' => $weights,
            'kmkk_aggregation_method' => $periodSummary?->aggregation_method,
            'kmkk_aggregation_computed_at' => $periodSummary?->aggregation_computed_at,
        ];
    }

    public function calculateAndSave(int $periodId, int $passCount = 0): array
    {
        $result = $this->getCalculationData($periodId);

        if (isset($result['error'])) {
            return $result;
        }

        $applicants = $result['applicants'];
        $rankings = $result['rankings'];
        $preferences = $result['preferences'];
        $distances = $result['distances'];

        SelectionResult::where('period_id', $periodId)->delete();
        foreach ($applicants as $applicant) {
            $currentRank = $rankings[$applicant->id];
            SelectionResult::create([
                'period_id' => $periodId,
                'applicant_id' => $applicant->id,
                'preference_value' => round($preferences[$applicant->id], 6),
                'positive_distance' => round($distances[$applicant->id]['positive'], 6),
                'negative_distance' => round($distances[$applicant->id]['negative'], 6),
                'rank' => $currentRank,
                'status' => ($passCount > 0 && $currentRank <= $passCount) ? 'lulus' : 'tidak_lulus',
            ]);
        }

        return $result;
    }
}
