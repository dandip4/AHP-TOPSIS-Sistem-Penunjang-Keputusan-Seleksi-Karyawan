<?php

namespace App\Services;

use App\Models\AggregatedEvaluation;
use App\Models\Applicant;
use App\Models\Evaluation;
use App\Models\SelectionPeriod;
use Illuminate\Support\Collection;

class GroupDecisionAggregator
{
    /** Hapus hasil agregat sehingga TOPSIS memaksa rebuild setelah ada perubahan penilaian. */
    public function clearAggregatesForPeriod(int $periodId): void
    {
        AggregatedEvaluation::where('period_id', $periodId)->delete();

        SelectionPeriod::where('id', $periodId)->update([
            'aggregation_computed_at' => null,
        ]);
    }

    /**
     * Bangun ulang matriks agregat untuk periode (input TOPSIS berikutnya).
     *
     * @return array{cells: int, method: string}|array{error: string}
     */
    public function rebuild(int $periodId, ?string $aggregationMethod = null, ?float $owaAlpha = null): array
    {
        $period = SelectionPeriod::find($periodId);

        if (! $period) {
            return ['error' => 'Periode tidak ditemukan.'];
        }

        $method = $aggregationMethod ?? $period->aggregation_method ?? 'average';
        if (! in_array($method, ['average', 'owa_most'], true)) {
            $method = 'average';
        }

        $alpha = $owaAlpha ?? (float) $period->owa_alpha;
        $alpha = max(1.01, $alpha);

        /** @var Collection<int, Applicant> $applicants */
        $applicants = Applicant::where('period_id', $periodId)->get();
        /** @var Collection<int, int> $criteriaIds */
        $criteriaIds = $period->linkedCriteria()->pluck('criteria.id');

        if ($applicants->isEmpty() || $criteriaIds->isEmpty()) {
            return ['error' => 'Tidak ada pelamar atau periode ini belum memiliki kriteria terpilih. Atur kriteria pada data periode seleksi.'];
        }

        AggregatedEvaluation::where('period_id', $periodId)->delete();

        $cells = 0;

        foreach ($applicants as $applicant) {
            foreach ($criteriaIds as $cId) {
                $scores = Evaluation::where('period_id', $periodId)
                    ->where('applicant_id', $applicant->id)
                    ->where('criteria_id', $cId)
                    ->pluck('score')
                    ->map(fn ($s) => (float) $s)
                    ->all();

                if ($scores === []) {
                    continue;
                }

                $agg = match ($method) {
                    'owa_most' => $this->linguisticOwaAscending($scores, $alpha),
                    default => round(array_sum($scores) / count($scores), 6),
                };

                AggregatedEvaluation::create([
                    'period_id' => $periodId,
                    'applicant_id' => $applicant->id,
                    'criteria_id' => $cId,
                    'aggregated_score' => round((float) $agg, 4),
                    'aggregation_method' => $method,
                    'evaluator_count_used' => count($scores),
                ]);
                ++$cells;
            }
        }

        $expected = $applicants->count() * $criteriaIds->count();

        if ($cells < $expected) {
            AggregatedEvaluation::where('period_id', $periodId)->delete();
            SelectionPeriod::where('id', $periodId)->update(['aggregation_computed_at' => null]);

            return [
                'error' => sprintf(
                    'Masih ada penilaian yang kosong. Harus terisi untuk setiap pasangan pelamar–kriteria (minimal satu evaluator per sel). Terisi %d dari %d sel.',
                    $cells,
                    $expected
                ),
            ];
        }

        SelectionPeriod::where('id', $periodId)->update([
            'aggregation_method' => $method,
            'owa_alpha' => $alpha,
            'aggregation_computed_at' => now(),
        ]);

        return ['cells' => $cells, 'method' => $method];
    }

    public function isAggregateMatrixComplete(int $periodId): bool
    {
        $applicantCount = Applicant::where('period_id', $periodId)->count();
        $criteriaCount = SelectionPeriod::find($periodId)?->linkedCriteria()->count() ?? 0;
        $expected = $applicantCount * $criteriaCount;

        if ($expected === 0) {
            return false;
        }

        return AggregatedEvaluation::where('period_id', $periodId)->count() >= $expected;
    }

    /**
     * OWA berbasis kuantifikasi linguistik Yager:
     * Q(r) = r^α dengan α > 1. Nilai diurut naik (s₁ ≤ … ≤ sₙ), bobot wⱼ = Q(j/n) − Q((j−1)/n).
     */
    public function linguisticOwaAscending(array $scores, float $alpha): float
    {
        $scores = array_values($scores);
        sort($scores, SORT_NUMERIC);
        $n = count($scores);

        if ($n === 0) {
            return 0.0;
        }

        if ($n === 1) {
            return round((float) $scores[0], 6);
        }

        $alpha = max(1.01, $alpha);

        $sum = 0.0;
        for ($j = 1; $j <= $n; ++$j) {
            $w = pow($j / $n, $alpha) - pow(($j - 1) / $n, $alpha);
            $sum += $w * (float) $scores[$j - 1];
        }

        return round($sum, 6);
    }
}
