<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$featureMap = [
    'education' => ['TU1', 'IT1'],
    'communication' => ['TU2', 'IT4'],
    'documentation' => ['TU3'],
    'technical' => ['TU4', 'IT2', 'IT7'],
    'experience' => ['TU6', 'IT6'],
    'interview' => ['TU7', 'IT5'],
];

function groupScores($rows, $featureMap) {
    $featureScores = [];
    foreach ($rows as $row) {
        $code = $row->criteria?->code;
        if (! $code) continue;
        foreach ($featureMap as $feat => $codes) {
            if (in_array($code, $codes, true)) {
                $featureScores[$feat][] = (float) $row->aggregated_score;
                break;
            }
        }
    }
    $result = [];
    foreach ($featureScores as $feat => $values) {
        $result[$feat] = max(1, min(5, (int) round(array_sum($values) / count($values))));
    }
    return $result;
}

$periodIds = [3,4,6,8,9,10];
foreach (App\Models\SelectionPeriod::whereIn('id', $periodIds)->orderBy('id')->get() as $p) {
    echo "--- Period {$p->id}: {$p->name}\n";
    $model = app(App\Services\BayesPredictor::class)->train($p->id, 5);
    if (isset($model['error'])) {
        echo "  ERROR: {$model['error']}\n";
        continue;
    }
    $predictions = app(App\Services\BayesPredictor::class)->predict($p->id, $model);
    $comp = app(App\Services\BayesPredictor::class)->compareWithTopsis($p->id, $predictions['predictions'] ?? []);
    echo "  Consistency: {$comp['consistency_percentage']}% ({$comp['matches']}/{$comp['total_predictions']})\n";
    foreach (($comp['discrepancies'] ?? []) as $dis) {
        echo "  MISSMATCH: {$dis['applicant_name']} bayes={$dis['bayes_prediction']} ({$dis['bayes_confidence']}%) topsis={$dis['topsis_status']} rank={$dis['topsis_rank']}\n";
        $applicantId = $dis['applicant_id'];
        $rows = App\Models\AggregatedEvaluation::where('period_id', $p->id)->with('criteria')->where('applicant_id', $applicantId)->get();
        $features = groupScores($rows, $featureMap);
        foreach ($features as $feat => $score) {
            echo "    {$feat}: {$score}\n";
        }
    }
}
