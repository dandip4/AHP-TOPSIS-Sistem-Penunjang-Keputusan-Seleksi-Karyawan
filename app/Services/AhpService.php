<?php

namespace App\Services;

use App\Models\Criteria;
use App\Models\CriteriaWeight;
use App\Models\PairwiseComparison;

class AhpService
{
    private const RANDOM_INDEX = [
        1 => 0.00, 2 => 0.00, 3 => 0.58, 4 => 0.90, 5 => 1.12,
        6 => 1.24, 7 => 1.32, 8 => 1.41, 9 => 1.45, 10 => 1.49,
        11 => 1.51, 12 => 1.48, 13 => 1.56, 14 => 1.57, 15 => 1.59,
    ];

    public function generatePairwiseFromImportance(int $periodId, array $criteriaIds): array
    {
        $criteria = Criteria::whereIn('id', $criteriaIds)->orderBy('importance', 'desc')->get();
        $n = $criteria->count();
        $matrix = [];

        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $impI = $criteria[$i]->importance;
                $impJ = $criteria[$j]->importance;

                if ($i === $j) {
                    $value = 1.0;
                } elseif ($impI >= $impJ) {
                    $value = $impI / $impJ;
                } else {
                    $value = $impJ / $impI;
                    $value = 1 / $value;
                }

                $matrix[$criteria[$i]->id][$criteria[$j]->id] = round($value, 4);
            }
        }

        return $matrix;
    }

    public function savePairwiseMatrix(int $periodId, array $matrix): void
    {
        PairwiseComparison::where('period_id', $periodId)->delete();

        foreach ($matrix as $rowId => $cols) {
            foreach ($cols as $colId => $value) {
                PairwiseComparison::create([
                    'period_id' => $periodId,
                    'criteria_row_id' => $rowId,
                    'criteria_col_id' => $colId,
                    'value' => $value,
                ]);
            }
        }
    }

    public function calculateWeights(int $periodId): array
    {
        $comparisons = PairwiseComparison::where('period_id', $periodId)->get();

        if ($comparisons->isEmpty()) {
            return ['error' => 'Tidak ada data perbandingan berpasangan'];
        }

        $criteriaIds = $comparisons->pluck('criteria_row_id')->unique()->sort()->values()->toArray();
        $n = count($criteriaIds);

        $matrix = [];
        foreach ($comparisons as $comp) {
            $matrix[$comp->criteria_row_id][$comp->criteria_col_id] = (float) $comp->value;
        }

        $colSums = [];
        foreach ($criteriaIds as $colId) {
            $sum = 0;
            foreach ($criteriaIds as $rowId) {
                $sum += $matrix[$rowId][$colId] ?? 0;
            }
            $colSums[$colId] = $sum;
        }

        $normalizedMatrix = [];
        foreach ($criteriaIds as $rowId) {
            foreach ($criteriaIds as $colId) {
                $normalizedMatrix[$rowId][$colId] = $colSums[$colId] > 0
                    ? ($matrix[$rowId][$colId] ?? 0) / $colSums[$colId]
                    : 0;
            }
        }

        $weights = [];
        foreach ($criteriaIds as $rowId) {
            $rowSum = array_sum($normalizedMatrix[$rowId]);
            $weights[$rowId] = $rowSum / $n;
        }

        $consistencyMatrix = [];
        $weightedSum = [];
        foreach ($criteriaIds as $rowId) {
            $sum = 0;
            foreach ($criteriaIds as $colId) {
                $val = ($matrix[$rowId][$colId] ?? 0) * $weights[$colId];
                $consistencyMatrix[$rowId][$colId] = $val;
                $sum += $val;
            }
            $weightedSum[$rowId] = $sum;
        }

        $lambdaValues = [];
        foreach ($criteriaIds as $id) {
            if ($weights[$id] > 0) {
                $lambdaValues[] = $weightedSum[$id] / $weights[$id];
            }
        }

        $lambdaMax = count($lambdaValues) > 0 ? array_sum($lambdaValues) / count($lambdaValues) : 0;
        $ci = $n > 1 ? ($lambdaMax - $n) / ($n - 1) : 0;
        $ri = self::RANDOM_INDEX[$n] ?? 1.59;
        $cr = $ri > 0 ? $ci / $ri : 0;

        CriteriaWeight::where('period_id', $periodId)->delete();
        foreach ($weights as $criteriaId => $weight) {
            CriteriaWeight::create([
                'period_id' => $periodId,
                'criteria_id' => $criteriaId,
                'weight' => round($weight, 6),
            ]);
        }

        return [
            'matrix' => $matrix,
            'col_sums' => $colSums,
            'normalized_matrix' => $normalizedMatrix,
            'weights' => $weights,
            'consistency_matrix' => $consistencyMatrix,
            'weighted_sum' => $weightedSum,
            'lambda_max' => round($lambdaMax, 4),
            'ci' => round($ci, 4),
            'ri' => $ri,
            'cr' => round($cr, 4),
            'is_consistent' => $cr <= 0.1,
            'criteria_ids' => $criteriaIds,
        ];
    }

    public function getRandomIndex(int $n): float
    {
        return self::RANDOM_INDEX[$n] ?? 1.59;
    }
}
