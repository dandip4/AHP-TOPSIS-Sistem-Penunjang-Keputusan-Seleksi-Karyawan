<?php

namespace App\Http\Controllers;

use App\Models\CriteriaWeight;
use App\Models\PairwiseComparison;
use App\Models\SelectionPeriod;
use App\Models\SelectionResult;
use App\Services\AhpService;
use App\Services\GroupDecisionAggregator;
use App\Services\TopsisService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CalculationController extends Controller
{
    public function __construct(
        private AhpService $ahpService,
        private TopsisService $topsisService,
        private GroupDecisionAggregator $groupDecisionAggregator,
    ) {}

    /**
     * @return Collection<int, \App\Models\Criteria>
     */
    private function linkedCriteriaWithSub(?int $periodId): Collection
    {
        if (! $periodId) {
            return collect();
        }

        $period = SelectionPeriod::find($periodId);

        return $period?->linkedCriteria()->with('subCriteria')->get() ?? collect();
    }

    public function ahp(Request $request)
    {
        $periods = SelectionPeriod::orderBy('name')->get();
        $periodId = $request->get('period_id');
        $criteria = collect();
        $ahpResult = null;
        /** @var \Illuminate\Support\Collection<int, CriteriaWeight>|null $weightsDefinedWithoutPairwise */
        $weightsDefinedWithoutPairwise = null;
        $selectedPeriod = null;

        if ($periodId) {
            $periodIdInt = (int) $periodId;
            $selectedPeriod = SelectionPeriod::find($periodIdInt);
            $criteria = $this->linkedCriteriaWithSub($periodIdInt);

            $hasPairwise = PairwiseComparison::where('period_id', $periodIdInt)->exists();
            if ($hasPairwise) {
                $ahpResult = $this->ahpService->calculateWeights($periodIdInt);
                if (isset($ahpResult['error'])) {
                    // Matriks tak lengkap atau korup → tampilkan bobot tersimpan sebagai fallback ringkas jika ada
                    $fallback = CriteriaWeight::where('period_id', $periodIdInt)->with('criteria')->get()->sortBy('criteria.code')->values();
                    $weightsDefinedWithoutPairwise = $fallback->isEmpty() ? null : $fallback;
                    $ahpResult = null;
                }
            } else {
                $loaded = CriteriaWeight::where('period_id', $periodIdInt)->with('criteria')->get()->sortBy('criteria.code')->values();
                $weightsDefinedWithoutPairwise = $loaded->isEmpty() ? null : $loaded;
            }
        }

        return view('BE.pages.calculations.ahp', compact(
            'periods', 'criteria', 'ahpResult', 'periodId', 'selectedPeriod', 'weightsDefinedWithoutPairwise'
        ));
    }

    public function calculateAhp(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:selection_periods,id',
        ]);

        $periodId = (int) $request->period_id;
        $periodModel = SelectionPeriod::findOrFail($periodId);
        $criteriaIds = $periodModel->linkedCriteria()->pluck('criteria.id')->sort()->values()->all();

        if ($criteriaIds === []) {
            return back()->with('error', 'Periode ini belum memiliki kriteria terpilih. Edit periode dan pilih minimal satu kriteria.');
        }

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
        $criteria = collect();
        $kmkkAggregateReady = false;

        if ($periodId) {
            $selectedPeriod = SelectionPeriod::find($periodId);
            $criteria = $this->linkedCriteriaWithSub((int) $periodId);
            $topsisResult = $this->topsisService->getCalculationData((int) $periodId);
            $kmkkAggregateReady = $this->groupDecisionAggregator->isAggregateMatrixComplete((int) $periodId);
        }

        return view('BE.pages.calculations.topsis', compact(
            'periods', 'topsisResult', 'periodId', 'selectedPeriod', 'criteria', 'kmkkAggregateReady'
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
        $criteria = collect();

        if ($periodId) {
            $selectedPeriod = SelectionPeriod::find($periodId);
            $criteria = $this->linkedCriteriaWithSub((int) $periodId);
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
