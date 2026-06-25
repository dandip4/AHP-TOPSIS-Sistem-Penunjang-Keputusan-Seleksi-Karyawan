<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Criteria;
use App\Models\CriteriaWeight;
use App\Models\Evaluation;
use App\Models\SelectionPeriod;
use App\Models\SelectionResult;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPeriods = SelectionPeriod::count();
        $activePeriods = SelectionPeriod::where('status', 'open')->count();
        $totalApplicants = Applicant::count();
        $totalCriteria = Criteria::active()->count();
        $totalLulus = SelectionResult::where('status', 'lulus')->count();
        $totalTidakLulus = SelectionResult::where('status', 'tidak_lulus')->count();

        // Analytics shortcuts
        $latestPeriod = SelectionPeriod::latest()->first();
        $analyticsAvailable = $latestPeriod ? true : false;
        $analyticsMessage = '';

        if ($analyticsAvailable) {
            $hasHistoricalData = SelectionPeriod::where('id', '!=', $latestPeriod->id)->count() >= 5;
            if (!$hasHistoricalData) {
                $analyticsMessage = 'Butuh 5+ periode untuk Naive Bayes prediction';
            }
        }

        $recentPeriods = SelectionPeriod::with('creator')
            ->latest()
            ->take(5)
            ->get();

        $latestResults = SelectionResult::with(['applicant', 'period'])
            ->latest()
            ->take(10)
            ->get();

        // Chart: Pelamar per Periode
        $applicantsPerPeriod = SelectionPeriod::withCount('applicants')
            ->orderBy('id')
            ->take(10)
            ->get()
            ->map(fn ($p) => [
                'name' => \Illuminate\Support\Str::limit($p->name, 25),
                'count' => $p->applicants_count,
            ]);

        // Chart: Distribusi Gender
        $genderDistribution = Applicant::select('gender', DB::raw('count(*) as total'))
            ->groupBy('gender')
            ->pluck('total', 'gender')
            ->toArray();

        // Chart: Distribusi Pendidikan
        $educationDistribution = Applicant::select('education', DB::raw('count(*) as total'))
            ->whereNotNull('education')
            ->groupBy('education')
            ->orderBy('total', 'desc')
            ->pluck('total', 'education')
            ->toArray();

        // Chart: Status Periode
        $periodStatusDistribution = SelectionPeriod::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Chart: Bobot Kriteria (dari periode terakhir yang sudah dihitung)
        $latestPeriodWithWeights = CriteriaWeight::select('period_id')
            ->latest()
            ->first();

        $criteriaWeightsChart = [];
        if ($latestPeriodWithWeights) {
            $criteriaWeightsChart = CriteriaWeight::where('period_id', $latestPeriodWithWeights->period_id)
                ->with('criteria')
                ->get()
                ->map(fn ($w) => [
                    'name' => $w->criteria->code . ' - ' . $w->criteria->name,
                    'code' => $w->criteria->code,
                    'weight' => round((float) $w->weight * 100, 2),
                ])
                ->toArray();
        }

        // Chart: Top 10 Ranking Pelamar (dari hasil terbaru)
        $latestPeriodWithResults = SelectionResult::select('period_id')
            ->latest()
            ->first();

        $topRankingChart = [];
        if ($latestPeriodWithResults) {
            $topRankingChart = SelectionResult::where('period_id', $latestPeriodWithResults->period_id)
                ->with('applicant')
                ->orderBy('rank')
                ->take(10)
                ->get()
                ->map(fn ($r) => [
                    'name' => $r->applicant->name,
                    'value' => round((float) $r->preference_value, 4),
                    'status' => $r->status,
                ])
                ->toArray();
        }

        // Chart: Rata-rata Skor per Kriteria (dari semua evaluasi)
        $avgScorePerCriteria = [];
        $criteriaList = Criteria::active()->orderBy('code')->get();
        if (Evaluation::exists()) {
            $avgScores = Evaluation::select('criteria_id', DB::raw('ROUND(AVG(score), 2) as avg_score'))
                ->groupBy('criteria_id')
                ->pluck('avg_score', 'criteria_id')
                ->toArray();

            foreach ($criteriaList as $c) {
                $avgScorePerCriteria[] = [
                    'code' => $c->code,
                    'name' => $c->name,
                    'avg' => (float) ($avgScores[$c->id] ?? 0),
                ];
            }
        }

        // Chart: Distribusi Skor (berapa pelamar yang dapat skor 1,2,3,4,5)
        $scoreDistribution = [];
        if (Evaluation::exists()) {
            $scoreDistribution = Evaluation::select(
                    DB::raw('CAST(score AS UNSIGNED) as score_val'),
                    DB::raw('count(*) as total')
                )
                ->groupBy('score_val')
                ->orderBy('score_val')
                ->pluck('total', 'score_val')
                ->toArray();
        }

        return view('BE.pages.dashboard', compact(
            'totalPeriods', 'activePeriods', 'totalApplicants',
            'totalCriteria', 'totalLulus', 'totalTidakLulus',
            'recentPeriods', 'latestResults',
            'applicantsPerPeriod', 'genderDistribution',
            'educationDistribution', 'periodStatusDistribution',
            'criteriaWeightsChart', 'topRankingChart',
            'avgScorePerCriteria', 'scoreDistribution',
            'analyticsAvailable', 'latestPeriod', 'analyticsMessage'
        ));
    }
}
