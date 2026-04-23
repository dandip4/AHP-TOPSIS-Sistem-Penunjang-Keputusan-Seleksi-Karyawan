<?php

namespace App\Http\Controllers;

use App\Models\Criteria;
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
        $results = collect();
        $selectedPeriod = null;
        $criteria = Criteria::active()->orderBy('code')->get();
        $weights = collect();
        $evaluations = collect();

        if ($periodId) {
            $selectedPeriod = SelectionPeriod::with('creator')->find($periodId);
            $results = SelectionResult::where('period_id', $periodId)
                ->with('applicant')
                ->orderBy('rank')
                ->get();
            $weights = CriteriaWeight::where('period_id', $periodId)
                ->with('criteria')
                ->get()
                ->keyBy('criteria_id');
            $evaluations = Evaluation::where('period_id', $periodId)
                ->with('applicant')
                ->get()
                ->groupBy('applicant_id');
        }

        return view('BE.pages.reports.index', compact(
            'periods', 'results', 'periodId', 'selectedPeriod',
            'criteria', 'weights', 'evaluations'
        ));
    }

    public function print(SelectionPeriod $period)
    {
        $period->load('creator');
        $results = SelectionResult::where('period_id', $period->id)
            ->with('applicant')
            ->orderBy('rank')
            ->get();
        $criteria = Criteria::active()->orderBy('code')->get();
        $weights = CriteriaWeight::where('period_id', $period->id)
            ->with('criteria')
            ->get()
            ->keyBy('criteria_id');
        $evaluations = Evaluation::where('period_id', $period->id)
            ->with('applicant')
            ->get()
            ->groupBy('applicant_id');

        return view('BE.pages.reports.print', compact(
            'period', 'results', 'criteria', 'weights', 'evaluations'
        ));
    }
}
