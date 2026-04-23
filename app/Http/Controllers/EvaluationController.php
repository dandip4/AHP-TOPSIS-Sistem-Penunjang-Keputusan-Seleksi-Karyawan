<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\SelectionPeriod;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    public function index(Request $request)
    {
        $periods = SelectionPeriod::orderBy('name')->get();
        $periodId = $request->get('period_id');
        $applicants = collect();
        $criteria = Criteria::active()->with('subCriteria')->orderBy('code')->get();
        $evaluations = collect();
        $selectedPeriod = null;

        if ($periodId) {
            $selectedPeriod = SelectionPeriod::find($periodId);
            $applicants = Applicant::where('period_id', $periodId)->orderBy('name')->get();
            $evaluations = Evaluation::where('period_id', $periodId)->get()
                ->groupBy('applicant_id');
        }

        return view('BE.pages.evaluations.index', compact(
            'periods', 'applicants', 'criteria', 'evaluations', 'periodId', 'selectedPeriod'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:selection_periods,id',
            'scores' => 'required|array',
            'scores.*.*' => 'required|numeric|min:1|max:5',
        ]);

        $periodId = $request->period_id;

        Evaluation::where('period_id', $periodId)->delete();

        foreach ($request->scores as $applicantId => $criteriaScores) {
            foreach ($criteriaScores as $criteriaId => $score) {
                Evaluation::create([
                    'period_id' => $periodId,
                    'applicant_id' => $applicantId,
                    'criteria_id' => $criteriaId,
                    'score' => $score,
                ]);
            }
        }

        return redirect()->route('evaluations.index', ['period_id' => $periodId])
            ->with('success', 'Penilaian berhasil disimpan.');
    }
}
