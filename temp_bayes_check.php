<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$predictor = app(App\Services\BayesPredictor::class);
foreach (App\Models\SelectionPeriod::orderBy('id')->get() as $p) {
    $model = $predictor->train($p->id, 5);
    if (isset($model['error'])) {
        echo $p->id . ' ' . $p->name . ' => ERROR: ' . $model['error'] . PHP_EOL;
        continue;
    }
    $pred = $predictor->predict($p->id, $model);
    $comp = $predictor->compareWithTopsis($p->id, $pred['predictions'] ?? []);
    echo $p->id . ' ' . $p->name . ' => ' . $comp['consistency_percentage'] . '% (' . ($comp['matches'] ?? 0) . '/' . ($comp['total_predictions'] ?? 0) . ')' . PHP_EOL;
}
