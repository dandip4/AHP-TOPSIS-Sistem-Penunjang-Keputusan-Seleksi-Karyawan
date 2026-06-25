<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$predictor = app(App\Services\BayesPredictor::class);
$periodIds = [3,4,6,8,9,10];
foreach (App\Models\SelectionPeriod::whereIn('id', $periodIds)->orderBy('id')->get() as $p) {
    echo '--- Period ' . $p->id . ': ' . $p->name . PHP_EOL;
    $model = $predictor->train($p->id, 5);
    if (isset($model['error'])) {
        echo '  ERROR: ' . $model['error'] . PHP_EOL;
        continue;
    }
    echo '  Training periods: ' . implode(',', $model['training_periods']) . PHP_EOL;
    echo '  Training counts: total=' . $model['training_count'] . ' lulus=' . $model['lulus_count'] . ' tidak_lulus=' . $model['tidak_lulus_count'] . PHP_EOL;
    $predictions = $predictor->predict($p->id, $model);
    $comp = $predictor->compareWithTopsis($p->id, $predictions['predictions'] ?? []);
    echo '  Consistency: ' . $comp['consistency_percentage'] . '% (' . $comp['matches'] . '/' . $comp['total_predictions'] . ')' . PHP_EOL;
    foreach (($comp['discrepancies'] ?? []) as $discrepancy) {
        echo '    MISSMATCH: ' . $discrepancy['applicant_name'] . ' bayes=' . $discrepancy['bayes_prediction'] . ' (' . $discrepancy['bayes_confidence'] . '%) topsis=' . $discrepancy['topsis_status'] . ' rank=' . $discrepancy['topsis_rank'] . PHP_EOL;
        $applicant = App\Models\Applicant::find($discrepancy['applicant_id']);
        echo '      Applicant scores feature-wise:' . PHP_EOL;
        $aggregates = App\Models\AggregatedEvaluation::where('period_id', $p->id)->with('criteria')->where('applicant_id', $applicant->id)->get();
        foreach ($predictor->groupAggregatedScoresByFeature($aggregates) as $feature => $score) {
            echo '        ' . $feature . ' => ' . $score . PHP_EOL;
        }
    }
}
