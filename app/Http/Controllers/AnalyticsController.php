<?php

namespace App\Http\Controllers;

use App\Models\SelectionPeriod;
use App\Services\AnalyticsService;
use App\Services\BayesPredictor;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analyticsService,
        private readonly BayesPredictor $bayesPredictor,
    ) {}

    /**
     * Dashboard utama analytics & insights
     */
    public function index(Request $request)
    {
        $periods = SelectionPeriod::orderBy('name')->get();
        $periodId = $request->get('period_id');

        $scoreDistribution = [];
        $outliers = [];
        $evaluatorStats = [];
        $discriminativeCriteria = [];
        $qualityFlags = [];
        $selectedPeriod = null;

        if ($periodId) {
            $selectedPeriod = SelectionPeriod::find($periodId);
            $scoreDistribution = $this->analyticsService->describeScoreDistribution($periodId);
            $outlierResult = $this->analyticsService->detectOutliers($periodId);
            $outliers = $outlierResult['outliers'] ?? [];
            $evaluatorStats = $this->analyticsService->analyzeEvaluatorVariability($periodId);
            $discriminativeCriteria = $this->analyticsService->analyzeDiscriminativeCriteria($periodId);
            $qualityControlResult = $this->analyticsService->qualityControlReport($periodId);
            $qualityFlags = $qualityControlResult['quality_flags'] ?? [];
        }

        return view('BE.pages.analytics.index', compact(
            'periods', 'periodId', 'selectedPeriod',
            'scoreDistribution', 'outliers',
            'evaluatorStats', 'discriminativeCriteria',
            'qualityFlags'
        ));
    }

    /**
     * Trend analysis dari multiple periods
     */
    public function trend(Request $request)
    {
        $lookbackPeriods = (int) $request->get('lookback_periods', 10);
        $trendData = $this->analyticsService->analyzeTrend($lookbackPeriods);

        return view('BE.pages.analytics.trend', compact('trendData', 'lookbackPeriods'));
    }

    /**
     * Naive Bayes prediction untuk periode tertentu
     */
    public function prediction(Request $request)
    {
        $periods = SelectionPeriod::orderBy('name')->get();
        $periodId = $request->get('period_id');

        $bayesResults = null;
        $comparisonWithTopsis = null;
        $bayesModelError = null;
        $selectedPeriod = null;

        if ($periodId) {
            $selectedPeriod = SelectionPeriod::find($periodId);

            // Train model dari periode sebelumnya
            $trainModel = $this->bayesPredictor->train((int) $periodId, 5);

            if (isset($trainModel['error'])) {
                $bayesModelError = $trainModel['error'];
            } else {
                // Prediksi untuk periode sekarang
                $predictions = $this->bayesPredictor->predict((int) $periodId, $trainModel);
                $bayesResults = $predictions['predictions'] ?? [];

                // Bandingkan dengan TOPSIS
                if (!empty($bayesResults)) {
                    $comparison = $this->bayesPredictor->compareWithTopsis((int) $periodId, $bayesResults);
                    $comparisonWithTopsis = $comparison;
                }
            }
        }

        return view('BE.pages.analytics.prediction', compact(
            'periods', 'periodId', 'selectedPeriod',
            'bayesResults', 'comparisonWithTopsis', 'bayesModelError'
        ));
    }

    /**
     * Sensitivity analysis
     */
    public function sensitivity(Request $request)
    {
        $periods = SelectionPeriod::orderBy('name')->get();
        $periodId = $request->get('period_id');
        $percentageChanges = [10, 20, -10, -20];

        $sensitivityResults = null;
        $selectedPeriod = null;

        if ($periodId) {
            $selectedPeriod = SelectionPeriod::find($periodId);
            $analysisResult = $this->analyticsService->sensitivityAnalysis((int) $periodId, $percentageChanges);
            $sensitivityResults = $analysisResult['sensitivity_results'] ?? null;
        }

        return view('BE.pages.analytics.sensitivity', compact(
            'periods', 'periodId', 'selectedPeriod',
            'sensitivityResults', 'percentageChanges'
        ));
    }

    /**
     * Quality control report
     */
    public function qualityControl(Request $request)
    {
        $periods = SelectionPeriod::orderBy('name')->get();
        $periodId = $request->get('period_id');

        $qualityReport = null;
        $selectedPeriod = null;

        if ($periodId) {
            $selectedPeriod = SelectionPeriod::find($periodId);
            $qualityReportResult = $this->analyticsService->qualityControlReport($periodId);
            $qualityReport = $qualityReportResult['quality_flags'] ?? [];
        }

        return view('BE.pages.analytics.quality-control', compact(
            'periods', 'periodId', 'selectedPeriod', 'qualityReport'
        ));
    }

    /**
     * API endpoint untuk chart data (JSON)
     */
    public function chartScoreDistribution(Request $request)
    {
        $periodId = (int) $request->get('period_id');
        if (!$periodId) {
            return response()->json(['error' => 'period_id required'], 400);
        }

        $data = $this->analyticsService->describeScoreDistribution($periodId);
        $chartData = [];

        foreach ($data['criteria_stats'] as $stat) {
            $chartData[] = [
                'name' => $stat['criteria_code'] . ' - ' . $stat['criteria_name'],
                'mean' => $stat['mean'],
                'stdev' => $stat['stdev'],
                'min' => $stat['min'],
                'max' => $stat['max'],
                'count' => $stat['count'],
            ];
        }

        return response()->json($chartData);
    }

    /**
     * API endpoint untuk evaluator performance (JSON)
     */
    public function chartEvaluatorPerformance(Request $request)
    {
        $periodId = (int) $request->get('period_id');
        if (!$periodId) {
            return response()->json(['error' => 'period_id required'], 400);
        }

        $data = $this->analyticsService->analyzeEvaluatorVariability($periodId);
        $chartData = [];

        foreach ($data['evaluator_stats'] as $stat) {
            $chartData[] = [
                'name' => $stat['evaluator_name'],
                'avg_score' => $stat['avg_score'],
                'stdev' => $stat['stdev'],
                'stringency' => $stat['stringency'],
            ];
        }

        return response()->json($chartData);
    }

    /**
     * API endpoint untuk trend data (JSON)
     */
    public function chartTrend(Request $request)
    {
        $lookbackPeriods = (int) $request->get('lookback_periods', 10);
        $data = $this->analyticsService->analyzeTrend($lookbackPeriods);

        // Format untuk line chart
        $passRateTrend = [];
        foreach ($data['pass_rate_trend'] as $item) {
            $passRateTrend[] = [
                'period' => $item['period_name'],
                'pass_rate' => $item['pass_rate_percentage'],
                'total' => $item['total_applicants'],
            ];
        }

        return response()->json(['pass_rate_trend' => $passRateTrend]);
    }
}
