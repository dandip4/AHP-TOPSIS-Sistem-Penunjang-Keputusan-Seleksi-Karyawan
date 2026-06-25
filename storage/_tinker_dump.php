$p = \App\Models\SelectionPeriod::orderBy('id')->first();
echo "PID=".$p->id.PHP_EOL;
$ap = $p->applicants()->orderBy('id')->first();
echo "PEL=".$ap->name.PHP_EOL;
$c = $p->linkedCriteria()->orderByPivot('sort_order')->first();
echo "KRITERIA=".$c->code.PHP_EOL;
$evs = \App\Models\Evaluation::where('period_id',$p->id)->where('applicant_id',$ap->id)->where('criteria_id',$c->id)->with('evaluator')->get();
foreach ($evs as $e) { echo $e->evaluator->code.":".$e->score." "; }
echo PHP_EOL;
$agg = \App\Models\AggregatedEvaluation::where('period_id',$p->id)->where('applicant_id',$ap->id)->where('criteria_id',$c->id)->first();
echo "AGG=".$agg->aggregated_score.PHP_EOL;
$w = \App\Models\CriteriaWeight::where('period_id',$p->id)->with('criteria')->get()->sortBy('criteria.code')->values();
foreach ($w as $row) { echo $row->criteria->code.' w='.sprintf('%.6f',$row->weight).PHP_EOL; }
$s = app(\App\Services\TopsisService::class);
$s->calculateAndSave((int)$p->id, 0);
foreach (\App\Models\SelectionResult::where('period_id',$p->id)->orderBy('rank')->get() as $sr) {
  $n = \App\Models\Applicant::find($sr->applicant_id)->name;
  echo $sr->rank.'|'.$n.'|'.sprintf('%0.6f',$sr->preference_value).PHP_EOL;
}
