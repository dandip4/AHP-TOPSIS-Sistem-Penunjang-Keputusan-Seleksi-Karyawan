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
    $results = $predictor->compareWithTopsis($p->id, $predictions['predictions'] ?? []);
    echo '  Consistency: ' . $results['consistency_percentage'] . '% (' . $results['matches'] . '/' . $results['total_predictions'] . ')' . PHP_EOL;
    foreach (($results['discrepancies'] ?? []) as $discrepancy) {
        echo '    MISSMATCH: ' . $discrepancy['applicant_name'] . ' bayes=' . $discrepancy['bayes_prediction'] . ' (' . $discrepancy['bayes_confidence'] . '%) topsis=' . $discrepancy['topsis_status'] . ' rank=' . $discrepancy['topsis_rank'] . PHP_EOL;
    }
}
