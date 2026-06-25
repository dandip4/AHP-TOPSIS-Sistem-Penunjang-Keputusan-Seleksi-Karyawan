<?php

namespace App\Services;

use App\Models\AggregatedEvaluation;
use App\Models\Applicant;
use App\Models\CriteriaWeight;
use App\Models\SelectionPeriod;
use App\Models\SelectionResult;
use Illuminate\Support\Collection;

class BayesPredictor
{
    /** Laplace smoothing constant untuk menghindari zero probability */
    private const LAPLACE_SMOOTHING = 0.001;

    /**
     * Pemetaan kriteria spesifik pekerjaan ke fitur umum yang konsisten antar periode.
     */
    private const FEATURE_MAP = [
        'education' => ['TU1', 'IT1'],
        'communication' => ['TU2', 'IT4'],
        'documentation' => ['TU3'],
        'technical' => ['TU4', 'IT2', 'IT7'],
        'experience' => ['TU6', 'IT6'],
        'interview' => ['TU7', 'IT5'],
    ];

    /**
     * Train Naive Bayes model dari data historis
     *
     * @param  int  $excludePeriodId  ID periode yang ingin diprediksi (jangan include di training)
     * @param  int  $lookbackPeriods  Berapa banyak periode sebelumnya untuk training (default: 5)
     * @return array{priors: array, likelihoods: array, error?: string}
     */
    public function train(int $excludePeriodId, int $lookbackPeriods = 5): array
    {
        // Ambil N periode sebelumnya (sebelum excludePeriodId)
        $trainingPeriods = SelectionPeriod::where('id', '<', $excludePeriodId)
            ->orderByDesc('id')
            ->take($lookbackPeriods)
            ->pluck('id')
            ->toArray();

        if (empty($trainingPeriods)) {
            return ['error' => 'Data historis tidak cukup untuk training Naive Bayes. Butuh minimal ' . $lookbackPeriods . ' periode sebelumnya.'];
        }

        // Ambil hasil historis: selection_results + aggregated_evaluations
        $results = SelectionResult::whereIn('period_id', $trainingPeriods)
            ->with(['period', 'applicant'])
            ->get();

        if ($results->isEmpty()) {
            return ['error' => 'Tidak ada hasil historis untuk training.'];
        }

        // Hitung prior probabilities
        $lulusCount = $results->where('status', 'lulus')->count();
        $tidakLulusCount = $results->where('status', 'tidak_lulus')->count();
        $totalCount = $results->count();

        $priors = [
            'lulus' => ($lulusCount + self::LAPLACE_SMOOTHING) / ($totalCount + 2 * self::LAPLACE_SMOOTHING),
            'tidak_lulus' => ($tidakLulusCount + self::LAPLACE_SMOOTHING) / ($totalCount + 2 * self::LAPLACE_SMOOTHING),
        ];

        // Hitung likelihood untuk setiap kriteria & score combination
        $likelihoods = ['lulus' => [], 'tidak_lulus' => []];

        $aggregates = AggregatedEvaluation::whereIn('period_id', $trainingPeriods)
            ->with('criteria')
            ->get()
            ->groupBy(fn ($row) => $row->applicant_id);

        $criteriaWeightRows = CriteriaWeight::whereIn('period_id', $trainingPeriods)
            ->get()
            ->groupBy('criteria_id');

        $criteriaWeights = [];
        foreach ($criteriaWeightRows as $criteriaId => $rows) {
            $criteriaWeights[$criteriaId] = $rows->avg('weight');
        }

        foreach (['lulus', 'tidak_lulus'] as $state) {
            $likelihoods[$state] = [];
        }

        foreach ($results as $result) {
            $featureValues = $this->groupAggregatedScoresByFeature(
                $aggregates->get($result->applicant_id) ?? collect(),
                $criteriaWeights
            );
            if (empty($featureValues)) {
                continue;
            }

            foreach ($featureValues as $feature => $scoreBin) {
                if ($result->status === 'lulus') {
                    $likelihoods['lulus']["{$feature}_{$scoreBin}"] =
                        ($likelihoods['lulus']["{$feature}_{$scoreBin}"] ?? 0) + 1;
                } else {
                    $likelihoods['tidak_lulus']["{$feature}_{$scoreBin}"] =
                        ($likelihoods['tidak_lulus']["{$feature}_{$scoreBin}"] ?? 0) + 1;
                }
            }
        }

        foreach (array_keys(self::FEATURE_MAP) as $feature) {
            foreach ([1, 2, 3, 4, 5] as $scoreBin) {
                $key = "{$feature}_{$scoreBin}";
                $likelihoods['lulus'][$key] =
                    (($likelihoods['lulus'][$key] ?? 0) + self::LAPLACE_SMOOTHING) /
                    ($lulusCount + 5 * self::LAPLACE_SMOOTHING);
                $likelihoods['tidak_lulus'][$key] =
                    (($likelihoods['tidak_lulus'][$key] ?? 0) + self::LAPLACE_SMOOTHING) /
                    ($tidakLulusCount + 5 * self::LAPLACE_SMOOTHING);
            }
        }

        return [
            'priors' => $priors,
            'likelihoods' => $likelihoods,
            'training_periods' => $trainingPeriods,
            'training_count' => $totalCount,
            'lulus_count' => $lulusCount,
            'tidak_lulus_count' => $tidakLulusCount,
        ];
    }

    /**
     * Prediksi kelulusan pelamar berdasarkan skor agregat mereka
     *
     * @param  int  $periodId  Periode target untuk prediksi
     * @param  array  $model  Training model dari method train()
     * @return array{predictions: array, training_info: array}
     */
    public function predict(int $periodId, array $model): array
    {
        if (isset($model['error'])) {
            return ['error' => $model['error']];
        }

        $priors = $model['priors'] ?? [];
        $likelihoods = $model['likelihoods'] ?? [];

        if (empty($priors) || empty($likelihoods)) {
            return ['error' => 'Model training invalid atau kosong.'];
        }

        // Ambil data periode target
        $applicants = Applicant::where('period_id', $periodId)->get();
        $aggregates = AggregatedEvaluation::where('period_id', $periodId)
            ->with('criteria')
            ->get()
            ->groupBy('applicant_id');

        $criteriaWeights = CriteriaWeight::where('period_id', $periodId)
            ->pluck('weight', 'criteria_id')
            ->toArray();

        $predictions = [];

        foreach ($applicants as $applicant) {
            $applicantAggregates = $aggregates->get($applicant->id) ?? collect();
            $featureValues = $this->groupAggregatedScoresByFeature($applicantAggregates, $criteriaWeights);

            if (empty($featureValues)) {
                continue;
            }

            // Hitung posterior probability untuk kedua kelas
            $probLulus = log($priors['lulus']);
            $probTidakLulus = log($priors['tidak_lulus']);

            foreach ($featureValues as $feature => $scoreBin) {
                $key = "{$feature}_{$scoreBin}";

                $probLulus += log($likelihoods['lulus'][$key] ?? self::LAPLACE_SMOOTHING);
                $probTidakLulus += log($likelihoods['tidak_lulus'][$key] ?? self::LAPLACE_SMOOTHING);
            }

            // Normalize probability
            $maxProb = max($probLulus, $probTidakLulus);
            $probLulus = exp($probLulus - $maxProb);
            $probTidakLulus = exp($probTidakLulus - $maxProb);

            $totalProb = $probLulus + $probTidakLulus;
            $confidenceLulus = $probLulus / $totalProb;
            $confidenceTidakLulus = $probTidakLulus / $totalProb;
            $predictedClass = $confidenceLulus >= 0.5 ? 'lulus' : 'tidak_lulus';
            $predictedConfidence = $predictedClass === 'lulus'
                ? $confidenceLulus
                : $confidenceTidakLulus;

            $predictions[$applicant->id] = [
                'applicant_id' => $applicant->id,
                'applicant_name' => $applicant->name,
                'confidence_lulus' => round($confidenceLulus * 100, 2),
                'confidence_tidak_lulus' => round($confidenceTidakLulus * 100, 2),
                'predicted_class' => $predictedClass,
                'predicted_confidence' => round($predictedConfidence * 100, 2),
                'probability_lulus' => round($confidenceLulus, 4),
                'probability_tidak_lulus' => round($confidenceTidakLulus, 4),
                'predicted_probability' => round($predictedConfidence, 4),
            ];
        }

        return [
            'predictions' => $predictions,
            'training_info' => [
                'training_count' => $model['training_count'] ?? 0,
                'lulus_count' => $model['lulus_count'] ?? 0,
                'tidak_lulus_count' => $model['tidak_lulus_count'] ?? 0,
                'training_periods' => $model['training_periods'] ?? [],
            ],
        ];
    }

    private function groupAggregatedScoresByFeature(
        \Illuminate\Support\Collection $aggregates,
        array $criteriaWeights = []
    ): array
    {
        $featureScores = [];
        $weightSums = [];

        foreach ($aggregates as $agg) {
            $criteriaCode = $agg->criteria?->code;
            if (! $criteriaCode) {
                continue;
            }

            $weight = $criteriaWeights[$agg->criteria_id] ?? 1;
            foreach (self::FEATURE_MAP as $feature => $codes) {
                if (in_array($criteriaCode, $codes, true)) {
                    $featureScores[$feature][] = (float) $agg->aggregated_score * $weight;
                    $weightSums[$feature] = ($weightSums[$feature] ?? 0) + $weight;
                    break;
                }
            }
        }

        $result = [];
        foreach ($featureScores as $feature => $values) {
            $totalWeight = $weightSums[$feature] ?? count($values);
            $average = $totalWeight > 0 ? array_sum($values) / $totalWeight : 0;
            $result[$feature] = max(1, min(5, (int) round($average)));
        }
        foreach ($aggregates as $agg) {
            $criteriaCode = $agg->criteria?->code;
            if (! $criteriaCode) {
                continue;
            }

            foreach (self::FEATURE_MAP as $feature => $codes) {
                if (in_array($criteriaCode, $codes, true)) {
                    $featureScores[$feature][] = (float) $agg->aggregated_score;
                    break;
                }
            }
        }

        $result = [];
        foreach ($featureScores as $feature => $values) {
            $result[$feature] = max(1, min(5, (int) round(array_sum($values) / count($values))));
        }

        return $result;
    }

    /**
     * Bandingkan prediksi Bayes dengan ranking TOPSIS
     * Identifikasi pelamar dengan diskrepansi tinggi
     *
     * @param  int  $periodId
     * @param  array  $predictions  Output dari method predict()
     * @return array{consistency: float, discrepancies: array}
     */
    public function compareWithTopsis(int $periodId, array $predictions): array
    {
        $results = SelectionResult::where('period_id', $periodId)
            ->orderBy('rank')
            ->get()
            ->keyBy('applicant_id');

        $discrepancies = [];
        $matchCount = 0;

        foreach ($predictions as $pred) {
            $applicantId = $pred['applicant_id'];
            $result = $results->get($applicantId);

            if (!$result) {
                continue;
            }

            $bayesPrediction = $pred['predicted_class'];
            $topsisPrediction = $result->status;
            $isMatch = $bayesPrediction === $topsisPrediction;

            if ($isMatch) {
                $matchCount++;
            } else {
                $discrepancies[] = [
                    'applicant_id' => $applicantId,
                    'applicant_name' => $pred['applicant_name'],
                        'bayes_confidence' => $pred['predicted_confidence'] ?? $pred['confidence_lulus'],
                    'bayes_prediction' => $bayesPrediction,
                    'bayes_probability' => $pred['predicted_probability'] ?? ($bayesPrediction === 'lulus' ? $pred['probability_lulus'] : $pred['probability_tidak_lulus']),
                    'topsis_rank' => $result->rank,
                    'topsis_status' => $topsisPrediction,
                    'preference_value' => round((float) $result->preference_value, 4),
                    'discrepancy_reason' => sprintf(
                        'Bayes %s namun TOPSIS %s',
                        $bayesPrediction,
                        $topsisPrediction
                    ),
                ];
            }
        }

        $consistency = count($predictions) > 0
            ? ($matchCount / count($predictions)) * 100
            : 0;

        return [
            'consistency_percentage' => round($consistency, 2),
            'total_predictions' => count($predictions),
            'matches' => $matchCount,
            'discrepancies' => $discrepancies,
            'discrepancy_count' => count($discrepancies),
        ];
    }
}
