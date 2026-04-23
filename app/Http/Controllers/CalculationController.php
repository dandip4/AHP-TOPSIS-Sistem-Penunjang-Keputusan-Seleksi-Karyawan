<?php

namespace App\Http\Controllers;

use App\Models\Criteria;
use App\Models\CriteriaWeight;
use App\Models\SelectionPeriod;
use App\Models\SelectionResult;
use App\Services\AhpService;
use App\Services\TopsisService;
use Illuminate\Http\Request;

class CalculationController extends Controller
{
    public function __construct(
        private AhpService $ahpService,
        private TopsisService $topsisService,
    ) {}

    public function ahp(Request $request)
    {
        $periods = SelectionPeriod::orderBy('name')->get();
        $periodId = $request->get('period_id');
        $criteria = Criteria::active()->orderBy('code')->get();
        $ahpResult = null;
        $selectedPeriod = null;

        if ($periodId) {
            $selectedPeriod = SelectionPeriod::find($periodId);
            $existingWeights = CriteriaWeight::where('period_id', $periodId)->count();

            if ($existingWeights > 0) {
                $ahpResult = $this->ahpService->calculateWeights($periodId);
            }
        }

        return view('BE.pages.calculations.ahp', compact(
            'periods', 'criteria', 'ahpResult', 'periodId', 'selectedPeriod'
        ));
    }

    public function calculateAhp(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:selection_periods,id',
        ]);

        $periodId = $request->period_id;
        $criteria = Criteria::active()->orderBy('code')->get();
        $criteriaIds = $criteria->pluck('id')->toArray();

        if ($request->has('matrix')) {
            $matrix = [];
            foreach ($request->matrix as $rowId => $cols) {
                foreach ($cols as $colId => $value) {
                    $matrix[$rowId][$colId] = (float) $value;
                }
            }
        } else {
            $matrix = $this->ahpService->generatePairwiseFromImportance($periodId, $criteriaIds);
        }

        $this->ahpService->savePairwiseMatrix($periodId, $matrix);
        $result = $this->ahpService->calculateWeights($periodId);

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        return redirect()->route('calculations.ahp', ['period_id' => $periodId])
            ->with('success', 'Perhitungan AHP berhasil! CR = ' . $result['cr'] .
                ($result['is_consistent'] ? ' (Konsisten)' : ' (Tidak Konsisten, CR > 0.1)'));
    }

    public function topsis(Request $request)
    {
        $periods = SelectionPeriod::orderBy('name')->get();
        $periodId = $request->get('period_id');
        $topsisResult = null;
        $selectedPeriod = null;
        $criteria = Criteria::active()->orderBy('code')->get();

        if ($periodId) {
            $selectedPeriod = SelectionPeriod::find($periodId);
            $existingResults = SelectionResult::where('period_id', $periodId)->exists();

            if ($existingResults) {
                $topsisResult = $this->topsisService->getCalculationData($periodId);
            }
        }

        return view('BE.pages.calculations.topsis', compact(
            'periods', 'topsisResult', 'periodId', 'selectedPeriod', 'criteria'
        ));
    }

    public function calculateTopsis(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:selection_periods,id',
            'pass_count' => 'nullable|integer|min:0',
        ]);

        $result = $this->topsisService->calculateAndSave(
            $request->period_id,
            (int) $request->get('pass_count', 0)
        );

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        return redirect()->route('calculations.topsis', ['period_id' => $request->period_id])
            ->with('success', 'Perhitungan TOPSIS berhasil! Perangkingan telah dibuat.');
    }

    public function results(Request $request)
    {
        $periods = SelectionPeriod::orderBy('name')->get();
        $periodId = $request->get('period_id');
        $results = collect();
        $selectedPeriod = null;
        $criteria = Criteria::active()->orderBy('code')->get();

        if ($periodId) {
            $selectedPeriod = SelectionPeriod::find($periodId);
            $results = SelectionResult::where('period_id', $periodId)
                ->with('applicant')
                ->orderBy('rank')
                ->get();
        }

        return view('BE.pages.calculations.results', compact(
            'periods', 'results', 'periodId', 'selectedPeriod', 'criteria'
        ));
    }
}
