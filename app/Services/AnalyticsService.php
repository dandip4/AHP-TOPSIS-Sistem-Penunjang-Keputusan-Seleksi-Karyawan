<?php

namespace App\Services;

use App\Models\AggregatedEvaluation;
use App\Models\Applicant;
use App\Models\Criteria;
use App\Models\CriteriaWeight;
use App\Models\Evaluation;
use App\Models\Evaluator;
use App\Models\SelectionPeriod;
use App\Models\SelectionResult;
use Illuminate\Support\Collection;

class AnalyticsService
{
    public function __construct(
        private readonly GroupDecisionAggregator $groupDecisionAggregator,
    ) {}

    /**
     * Hitung statistik deskriptif skor per kriteria untuk periode
     *
     * @param  int  $periodId
     * @return array{criteria: array, overall_stats: array}
     */
    public function describeScoreDistribution(int $periodId): array
    {
        $aggregates = AggregatedEvaluation::where('period_id', $periodId)
            ->with('criteria')
            ->get()
            ->groupBy('criteria_id');

        $criteriaStats = [];

        foreach ($aggregates as $criteriaId => $scores) {
            $values = $scores->map(fn ($s) => (float) $s->aggregated_score)->toArray();
            sort($values);
            $n = count($values);

            $mean = array_sum($values) / $n;
            $median = $n % 2 == 0
                ? ($values[$n/2 - 1] + $values[$n/2]) / 2
                : $values[($n-1)/2];

            $variance = 0;
            foreach ($values as $v) {
                $variance += pow($v - $mean, 2);
            }
            $variance /= $n;
            $stdev = sqrt($variance);

            $criteria = $scores->first()->criteria;

            $criteriaStats[] = [
                'criteria_id' => $criteriaId,
                'criteria_code' => $criteria->code,
                'criteria_name' => $criteria->name,
                'min' => round(min($values), 4),
                'max' => round(max($values), 4),
                'mean' => round($mean, 4),
                'median' => round($median, 4),
                'stdev' => round($stdev, 4),
                'count' => $n,
            ];
        }

        // Overall stats
        $allScores = AggregatedEvaluation::where('period_id', $periodId)
            ->pluck('aggregated_score')
            ->map(fn ($s) => (float) $s)
            ->toArray();

        $overallStats = [];
        if (!empty($allScores)) {
            sort($allScores);
            $n = count($allScores);
            $mean = array_sum($allScores) / $n;
            $median = $n % 2 == 0
                ? ($allScores[$n/2 - 1] + $allScores[$n/2]) / 2
                : $allScores[($n-1)/2];

            $variance = 0;
            foreach ($allScores as $v) {
                $variance += pow($v - $mean, 2);
            }
            $variance /= $n;

            $overallStats = [
                'min' => round(min($allScores), 4),
                'max' => round(max($allScores), 4),
                'mean' => round($mean, 4),
                'median' => round($median, 4),
                'stdev' => round(sqrt($variance), 4),
                'count' => $n,
            ];
        }

        return [
            'criteria_stats' => $criteriaStats,
            'overall_stats' => $overallStats,
        ];
    }

    /**
     * Identifikasi outlier dalam scoring
     * Gunakan IQR method (Interquartile Range)
     *
     * @param  int  $periodId
     * @return array{outliers: array, outlier_count: int}
     */
    public function detectOutliers(int $periodId): array
    {
        $aggregates = AggregatedEvaluation::where('period_id', $periodId)
            ->with(['applicant', 'criteria'])
            ->get();

        $outliers = [];

        // Group by criteria
        $byCriteria = $aggregates->groupBy('criteria_id');

        foreach ($byCriteria as $criteriaId => $scores) {
            $values = $scores->map(fn ($s) => (float) $s->aggregated_score)->toArray();
            sort($values);
            $n = count($values);

            if ($n < 4) continue; // IQR butuh minimal data

            // Q1, Q2, Q3
            $q1 = $values[intval(floor(($n - 1) * 0.25))];
            $q3 = $values[intval(floor(($n - 1) * 0.75))];
            $iqr = $q3 - $q1;

            $lowerBound = $q1 - 1.5 * $iqr;
            $upperBound = $q3 + 1.5 * $iqr;

            foreach ($scores as $score) {
                $val = (float) $score->aggregated_score;
                if ($val < $lowerBound || $val > $upperBound) {
                    $outliers[] = [
                        'applicant_id' => $score->applicant_id,
                        'applicant_name' => $score->applicant->name,
                        'criteria_id' => $criteriaId,
                        'criteria_code' => $score->criteria->code,
                        'criteria_name' => $score->criteria->name,
                        'score' => $val,
                        'lower_bound' => round($lowerBound, 4),
                        'upper_bound' => round($upperBound, 4),
                        'outlier_type' => $val < $lowerBound ? 'low' : 'high',
                    ];
                }
            }
        }

        return [
            'outliers' => $outliers,
            'outlier_count' => count($outliers),
        ];
    }

    /**
     * Analisis variabilitas evaluator (siapa yang paling stringent/lenient)
     *
     * @param  int  $periodId
     * @return array{evaluator_stats: array}
     */
    public function analyzeEvaluatorVariability(int $periodId): array
    {
        $evaluations = Evaluation::where('period_id', $periodId)
            ->with('evaluator')
            ->get()
            ->groupBy('evaluator_id');

        $evaluatorStats = [];

        foreach ($evaluations as $evaluatorId => $scores) {
            $values = $scores->map(fn ($e) => (float) $e->score)->toArray();
            $n = count($values);

            if ($n == 0) continue;

            $mean = array_sum($values) / $n;
            $variance = 0;
            foreach ($values as $v) {
                $variance += pow($v - $mean, 2);
            }
            $stdev = sqrt($variance / $n);

            $evaluator = $scores->first()->evaluator;

            $evaluatorStats[] = [
                'evaluator_id' => $evaluatorId,
                'evaluator_name' => $evaluator->name,
                'avg_score' => round($mean, 4),
                'stdev' => round($stdev, 4),
                'min_score' => round(min($values), 4),
                'max_score' => round(max($values), 4),
                'evaluation_count' => $n,
                'stringency' => $mean < 2.5 ? 'very_stringent' : ($mean < 3 ? 'stringent' : ($mean < 3.5 ? 'moderate' : ($mean < 4 ? 'lenient' : 'very_lenient'))),
            ];
        }

        // Sort by avg_score
        usort($evaluatorStats, fn ($a, $b) => $a['avg_score'] <=> $b['avg_score']);

        return ['evaluator_stats' => $evaluatorStats];
    }

    /**
     * Analisis trend historis: bobot kriteria dan persentase lulus per periode
     *
     * @param  int  $lookbackPeriods  Berapa banyak periode terakhir (default: 10)
     * @return array{weight_trend: array, pass_rate_trend: array}
     */
    public function analyzeTrend(int $lookbackPeriods = 10): array
    {
        // Trend bobot kriteria
        $periods = SelectionPeriod::orderByDesc('id')
            ->take($lookbackPeriods)
            ->get()
            ->sortBy('id');

        $weightTrend = [];
        $passRateTrend = [];

        foreach ($periods as $period) {
            $weights = CriteriaWeight::where('period_id', $period->id)
                ->with('criteria')
                ->get()
                ->sortBy('criteria.code');

            foreach ($weights as $weight) {
                $code = $weight->criteria->code;
                if (!isset($weightTrend[$code])) {
                    $weightTrend[$code] = [];
                }
                $weightTrend[$code][] = [
                    'period_id' => $period->id,
                    'period_name' => $period->name,
                    'weight' => round((float) $weight->weight, 4),
                ];
            }

            // Trend persentase lulus
            $results = SelectionResult::where('period_id', $period->id)->get();
            if ($results->isNotEmpty()) {
                $lulusCount = $results->where('status', 'lulus')->count();
                $passRate = ($lulusCount / $results->count()) * 100;
                $passRateTrend[] = [
                    'period_id' => $period->id,
                    'period_name' => $period->name,
                    'total_applicants' => $results->count(),
                    'lulus_count' => $lulusCount,
                    'pass_rate_percentage' => round($passRate, 2),
                ];
            }
        }

        return [
            'weight_trend' => $weightTrend,
            'pass_rate_trend' => $passRateTrend,
        ];
    }

    /**
     * Identifikasi kriteria paling diskriminatif (paling berpengaruh terhadap ranking)
     *
     * @param  int  $periodId
     * @return array{discriminative_criteria: array}
     */
    public function analyzeDiscriminativeCriteria(int $periodId): array
    {
        $criteria = CriteriaWeight::where('period_id', $periodId)
            ->with('criteria')
            ->get();

        $results = SelectionResult::where('period_id', $periodId)->get();
        $aggregates = AggregatedEvaluation::where('period_id', $periodId)->get();

        $discriminativeScores = [];

        foreach ($criteria as $weight) {
            $criteriaId = $weight->criteria_id;
            $criteriaCode = $weight->criteria->code;
            $criteriaName = $weight->criteria->name;

            $scores = $aggregates
                ->where('criteria_id', $criteriaId)
                ->map(fn ($a) => (float) $a->aggregated_score)
                ->toArray();

            if (empty($scores)) {
                continue;
            }

            sort($scores);
            $n = count($scores);

            // Calculate variance (higher variance = more discriminative)
            $mean = array_sum($scores) / $n;
            $variance = 0;
            foreach ($scores as $s) {
                $variance += pow($s - $mean, 2);
            }
            $variance /= $n;

            // Correlation dengan ranking (sederhana: higher score = better rank?)
            $correlationSum = 0;
            $ranking = SelectionResult::where('period_id', $periodId)
                ->with('applicant')
                ->orderBy('rank')
                ->get();

            foreach ($ranking as $result) {
                $agg = $aggregates->firstWhere(function ($a) use ($criteriaId, $result) {
                    return $a->criteria_id == $criteriaId && $a->applicant_id == $result->applicant_id;
                });

                if ($agg) {
                    // Simple correlation: multiply score with rank (inverse)
                    $correlationSum += (float) $agg->aggregated_score * (1 / $result->rank);
                }
            }

            $discriminativeScores[] = [
                'criteria_id' => $criteriaId,
                'criteria_code' => $criteriaCode,
                'criteria_name' => $criteriaName,
                'weight' => round((float) $weight->weight, 4),
                'variance' => round($variance, 4),
                'correlation_score' => round($correlationSum, 4),
                'discriminative_index' => round($variance * ((float) $weight->weight), 4),
            ];
        }

        // Sort by discriminative_index (highest first)
        usort($discriminativeScores, fn ($a, $b) => $b['discriminative_index'] <=> $a['discriminative_index']);

        return ['discriminative_criteria' => $discriminativeScores];
    }

    /**
     * Sensitivity Analysis: simulasi jika bobot berubah
     * Hitung berapa ranking berubah ketika bobot ±10%, ±20%, dsb
     *
     * @param  int  $periodId
     * @param  array  $percentageChanges  Contoh: [10, 20, -10, -20]
     * @return array{sensitivity_results: array}
     */
    public function sensitivityAnalysis(int $periodId, array $percentageChanges = [10, 20]): array
    {
        $topsisService = new TopsisService($this->groupDecisionAggregator);
        $originalData = $topsisService->getCalculationData($periodId);

        if (isset($originalData['error'])) {
            return ['error' => $originalData['error']];
        }

        $criteria = CriteriaWeight::where('period_id', $periodId)
            ->with('criteria')
            ->get();

        if ($criteria->isEmpty()) {
            return ['error' => 'Tidak ada bobot kriteria untuk periode ini'];
        }

        $originalRankings = $originalData['rankings'] ?? [];
        $applicants = $originalData['applicants'] ?? [];
        $decisionMatrix = $originalData['decision_matrix'] ?? [];

        $sensitivityResults = [];

        // Untuk setiap kriteria, hitung pengaruh perubahan bobotnya
        foreach ($criteria as $weightRow) {
            $criteriaId = $weightRow->criteria_id;
            $criteriaCode = $weightRow->criteria->code;
            $criteriaName = $weightRow->criteria->name;
            $currentWeight = (float) $weightRow->weight;

            $rankChangesByPercentage = [];
            $mostAffectedApplicants = [];
            $maxRankChange = 0;

            // Simulasi perubahan bobot untuk setiap percentage change
            foreach ($percentageChanges as $percentChange) {
                $modifiedWeights = [];

                foreach ($criteria as $w) {
                    if ($w->criteria_id === $criteriaId) {
                        // Ubah bobot kriteria ini
                        $modifiedWeights[$w->criteria_id] = (float) $w->weight * (1 + $percentChange / 100);
                    } else {
                        // Kriteria lain tetap
                        $modifiedWeights[$w->criteria_id] = (float) $w->weight;
                    }
                }

                // Normalize modified weights
                $totalWeight = array_sum($modifiedWeights);
                foreach ($modifiedWeights as $cId => &$w) {
                    $w = $w / $totalWeight;
                }

                // Recalculate preferences dengan modified weights
                $newPreferences = [];
                foreach ($applicants as $applicant) {
                    $score = 0;
                    foreach ($modifiedWeights as $cId => $w) {
                        $score += ($decisionMatrix[$applicant->id][$cId] ?? 0) * $w;
                    }
                    $newPreferences[$applicant->id] = $score;
                }

                arsort($newPreferences);
                $newRankings = [];
                $rank = 1;
                foreach ($newPreferences as $applicantId => $pref) {
                    $newRankings[$applicantId] = $rank++;
                }

                // Hitung perubahan ranking
                $improvedCount = 0;
                $declinedCount = 0;
                $rankChangeCount = 0;
                $applicantRankChanges = [];

                foreach ($originalRankings as $applicantId => $originalRank) {
                    $newRank = $newRankings[$applicantId] ?? 0;
                    $rankChange = $originalRank - $newRank; // Positif = improve, Negatif = decline

                    if ($rankChange > 0) {
                        $improvedCount++;
                    } elseif ($rankChange < 0) {
                        $declinedCount++;
                    }

                    if ($rankChange != 0) {
                        $rankChangeCount++;
                        $applicantRankChanges[$applicantId] = abs($rankChange);
                        $maxRankChange = max($maxRankChange, abs($rankChange));
                    }
                }

                $rankChangesByPercentage[$percentChange] = [
                    'rank_change_count' => $rankChangeCount,
                    'improved_count' => $improvedCount,
                    'declined_count' => $declinedCount,
                ];
            }

            // Identifikasi pelamar paling terpengaruh
            if (!empty($applicantRankChanges)) {
                arsort($applicantRankChanges);
                $topAffected = array_slice($applicantRankChanges, 0, 3, true);

                foreach ($topAffected as $applicantId => $maxChange) {
                    $applicant = $applicants->firstWhere('id', $applicantId);
                    if ($applicant) {
                        $mostAffectedApplicants[] = [
                            'applicant_id' => $applicantId,
                            'applicant_name' => $applicant->name,
                            'original_rank' => $originalRankings[$applicantId] ?? 0,
                            'max_rank_change' => $maxChange,
                            'worst_case_rank' => $originalRankings[$applicantId] - $maxChange,
                            'best_case_rank' => $originalRankings[$applicantId] + $maxChange,
                        ];
                    }
                }
            }

            // Tentukan sensitivity impact
            $impactLevel = $maxRankChange > 5 ? 'high' : ($maxRankChange > 2 ? 'medium' : 'low');
            $conclusion = match ($impactLevel) {
                'high' => "Kriteria ini sangat penting. Perubahan bobot menyebabkan perubahan ranking hingga $maxRankChange posisi.",
                'medium' => "Kriteria ini cukup berpengaruh. Perubahan bobot menyebabkan perubahan ranking hingga $maxRankChange posisi.",
                'low' => "Kriteria ini kurang sensitif. Perubahan bobot memiliki dampak minimal terhadap ranking.",
            };

            $sensitivityResults[$criteriaCode] = [
                'criteria_name' => $criteriaName,
                'current_weight' => $currentWeight,
                'rank_changes' => $rankChangesByPercentage,
                'most_affected_applicants' => $mostAffectedApplicants,
                'sensitivity_impact' => $impactLevel,
                'conclusion' => $conclusion,
            ];
        }

        return ['sensitivity_results' => $sensitivityResults];
    }

    /**
     * Generate quality control report
     * Flag evaluator abnormal dan pelamar dengan skor ekstrim
     *
     * @param  int  $periodId
     * @return array{quality_flags: array}
     */
    public function qualityControlReport(int $periodId): array
    {
        $flags = [];

        // Check 1: Evaluator dengan stdev terlalu tinggi (inconsistent scorer)
        $evaluatorStats = $this->analyzeEvaluatorVariability($periodId);
        foreach ($evaluatorStats['evaluator_stats'] as $eval) {
            if ($eval['stdev'] > 1.8) {
                $flags[] = [
                    'flag_type' => 'evaluator_high_variance',
                    'severity' => 'medium',
                    'evaluator_id' => $eval['evaluator_id'],
                    'evaluator_name' => $eval['evaluator_name'],
                    'issue' => 'Evaluator ini memiliki standar deviasi skor tinggi (' . $eval['stdev'] . '), mungkin scoring tidak konsisten',
                    'recommendation' => 'Review kembali penilaian evaluator ini, atau verifikasi pemahaman terhadap kriteria',
                ];
            }
        }

        // Check 2: Pelamar dengan skor ekstrim (outlier applicant)
        $outlierResult = $this->detectOutliers($periodId);
        foreach ($outlierResult['outliers'] as $outlier) {
            $flags[] = [
                'flag_type' => 'applicant_score_outlier',
                'severity' => 'low',
                'applicant_id' => $outlier['applicant_id'],
                'applicant_name' => $outlier['applicant_name'],
                'issue' => $outlier['applicant_name'] . ' memiliki skor ' . $outlier['outlier_type'] . ' untuk kriteria ' . $outlier['criteria_code'],
                'recommendation' => 'Verifikasi skor ini dengan tim, atau ada indikasi bias evaluasi',
            ];
        }

        // Check 3: Persentase lulus terlalu tinggi/rendah
        $results = SelectionResult::where('period_id', $periodId)->get();
        if ($results->isNotEmpty()) {
            $lulusRate = ($results->where('status', 'lulus')->count() / $results->count()) * 100;
            if ($lulusRate > 80) {
                $flags[] = [
                    'flag_type' => 'pass_rate_too_high',
                    'severity' => 'low',
                    'issue' => 'Persentase kelulusan tinggi (' . round($lulusRate, 2) . '%)',
                    'recommendation' => 'Review bobot kriteria atau threshold preferensi value TOPSIS',
                ];
            } elseif ($lulusRate < 10) {
                $flags[] = [
                    'flag_type' => 'pass_rate_too_low',
                    'severity' => 'low',
                    'issue' => 'Persentase kelulusan rendah (' . round($lulusRate, 2) . '%)',
                    'recommendation' => 'Review bobot kriteria atau kriteria mungkin terlalu ketat',
                ];
            }
        }

        return ['quality_flags' => $flags, 'flag_count' => count($flags)];
    }
}
