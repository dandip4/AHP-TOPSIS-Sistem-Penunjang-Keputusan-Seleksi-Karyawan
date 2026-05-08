<?php

namespace App\Http\Controllers;

use App\Models\AggregatedEvaluation;
use App\Models\Applicant;
use App\Models\CriteriaWeight;
use App\Models\Evaluation;
use App\Models\SelectionPeriod;
use App\Models\SelectionResult;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $periods = SelectionPeriod::orderBy('name')->get();
        $periodId = $request->get('period_id');
        /** @var int|null */
        $focusApplicantId = $request->integer('applicant_id') ?: null;
        $reportApplicants = collect();
        $evaluationsPeriodTotal = 0;
        $results = collect();
        $selectedPeriod = null;
        $criteria = collect();
        $weights = collect();
        /** @var \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, AggregatedEvaluation>>|\Illuminate\Support\Collection */
        $aggregatesByApplicant = collect();
        /** @var \Illuminate\Support\Collection|null $rawEvaluationsSample */
        $evaluations = collect();

        if ($periodId) {
            $selectedPeriod = SelectionPeriod::with('creator')->find($periodId);
            $criteria = $selectedPeriod !== null
                ? $selectedPeriod->linkedCriteria()->with('subCriteria')->orderByPivot('sort_order')->get()
                : collect();

            $reportApplicants = Applicant::where('period_id', $periodId)->orderBy('name')->get();
            if ($focusApplicantId && ! $reportApplicants->contains('id', $focusApplicantId)) {
                $focusApplicantId = null;
            }

            $results = SelectionResult::where('period_id', $periodId)
                ->with('applicant')
                ->orderBy('rank')
                ->get();
            $weights = CriteriaWeight::where('period_id', $periodId)
                ->with('criteria')
                ->get()
                ->keyBy('criteria_id');
            $aggregatesByApplicant = AggregatedEvaluation::where('period_id', $periodId)
                ->with(['applicant'])
                ->get()
                ->groupBy('applicant_id');

            $evaluationsPeriodTotal = Evaluation::where('period_id', $periodId)->count();

            // Penilaian mentah — hanya diambil bila ada filter pelamar (ringkas halaman)
            $evaluations = collect();
            if ($focusApplicantId) {
                $evaluations = Evaluation::where('period_id', $periodId)
                    ->where('applicant_id', $focusApplicantId)
                    ->with(['applicant', 'evaluator', 'criteria'])
                    ->orderBy('criteria_id')
                    ->orderBy('evaluator_id')
                    ->limit(2000)
                    ->get();
            }
        }

        return view('BE.pages.reports.index', compact(
            'periods', 'results', 'periodId', 'selectedPeriod',
            'criteria', 'weights', 'aggregatesByApplicant', 'evaluations',
            'reportApplicants', 'focusApplicantId', 'evaluationsPeriodTotal',
        ));
    }

    public function print(SelectionPeriod $period)
    {
        $period->load('creator');
        $results = SelectionResult::where('period_id', $period->id)
            ->with('applicant')
            ->orderBy('rank')
            ->get();
        $criteria = $period->linkedCriteria()->with('subCriteria')->orderByPivot('sort_order')->get();
        $weights = CriteriaWeight::where('period_id', $period->id)
            ->with('criteria')
            ->get()
            ->keyBy('criteria_id');

        $aggregatesByApplicant = AggregatedEvaluation::where('period_id', $period->id)
            ->with(['applicant'])
            ->get()
            ->groupBy('applicant_id');

        return view('BE.pages.reports.print', compact(
            'period', 'results', 'criteria', 'weights', 'aggregatesByApplicant'
        ));
    }
}
