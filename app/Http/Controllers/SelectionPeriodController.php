<?php

namespace App\Http\Controllers;

use App\Models\AggregatedEvaluation;
use App\Models\Criteria;
use App\Models\CriteriaWeight;
use App\Models\Evaluation;
use App\Models\Evaluator;
use App\Models\PairwiseComparison;
use App\Models\SelectionPeriod;
use App\Services\GroupDecisionAggregator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SelectionPeriodController extends Controller
{
    public function __construct(
        private readonly GroupDecisionAggregator $groupDecisionAggregator,
    ) {}

    public function index()
    {
        $periods = SelectionPeriod::with('creator')
            ->withCount('applicants')
            ->latest()
            ->get();

        return view('BE.pages.periods.index', compact('periods'));
    }

    public function create()
    {
        $criteriaMaster = Criteria::active()->with('subCriteria')->orderBy('code')->get();
        $evaluators = Evaluator::active()->get();

        return view('BE.pages.periods.create', compact('criteriaMaster', 'evaluators'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePeriodForm($request, false);

        DB::transaction(function () use ($request, $validated): void {
            $period = SelectionPeriod::create([
                ...$request->only('name', 'position', 'start_date', 'end_date', 'description'),
                'created_by' => Auth::id(),
            ]);
            $this->syncPeriodCriteriaAndWeights($period, $validated['criteria_ids'], $validated['weights_normalized']);
            // Attach evaluators to period
            if (! empty($validated['evaluator_ids'])) {
                $period->evaluators()->attach($validated['evaluator_ids']);
            }
        });

        return redirect()->route('periods.index')->with('success', 'Periode seleksi berhasil dibuat dengan kriteria dan bobot awal.');
    }

    /**
     * @return array{criteria_ids: array<int,int>, weights_normalized: array<int,float>, evaluator_ids: array<int,int>}
     */
    private function validatePeriodForm(Request $request, bool $isUpdate): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'description' => ['nullable', 'string'],
            'criteria_ids' => ['required', 'array', 'min:1'],
            'criteria_ids.*' => ['integer', 'distinct', Rule::exists('criteria', 'id')->where('is_active', true)],
            'weights' => ['required', 'array'],
            'evaluator_ids' => ['nullable', 'array'],
            'evaluator_ids.*' => ['integer', 'distinct', Rule::exists('evaluators', 'id')->where('is_active', true)],
        ];

        if ($isUpdate) {
            $rules['status'] = ['required', Rule::in(['draft', 'open', 'closed', 'completed'])];
        }

        $request->validate($rules);

        $idsOrdered = collect($request->criteria_ids)->map(static fn ($v): int => (int) $v)->uniqueStrict()->values()->all();

        foreach ($idsOrdered as $cid) {
            $w = $request->input("weights.$cid");
            if ($w === null || $w === '') {
                throw ValidationException::withMessages([
                    "weights.{$cid}" => 'Bobot wajib diisi untuk setiap kriteria terpilih.',
                ]);
            }
        }

        $sumRaw = 0.0;
        foreach ($idsOrdered as $cid) {
            $sumRaw += max(0.0, (float) $request->input("weights.$cid"));
        }

        if ($sumRaw <= 0) {
            throw ValidationException::withMessages([
                'weights' => 'Total bobot harus lebih dari 0 setelah dibaca dari input (gunakan angka lebih dari nol atau dinormalisasi).',
            ]);
        }

        $weightsNormalized = [];
        foreach ($idsOrdered as $cid) {
            $weightsNormalized[$cid] = round(max(0.0, (float) $request->input("weights.$cid")) / $sumRaw, 6);
        }

        $evaluatorIds = collect($request->evaluator_ids ?? [])->map(static fn ($v): int => (int) $v)->uniqueStrict()->values()->all();

        return [
            'criteria_ids' => $idsOrdered,
            'weights_normalized' => $weightsNormalized,
            'evaluator_ids' => $evaluatorIds,
        ];
    }

    /**
     * @param  array<int, int>  $orderedIds
     * @param  array<int, float>  $weightsNormalized
     */
    private function syncPeriodCriteriaAndWeights(SelectionPeriod $period, array $orderedIds, array $weightsNormalized): void
    {
        $syncData = [];
        foreach (array_values($orderedIds) as $i => $cid) {
            $syncData[$cid] = ['sort_order' => $i];
        }

        $oldIds = $period->linkedCriteria()->pluck('criteria.id')->all();
        $removed = array_diff($oldIds, $orderedIds);

        if ($removed !== []) {
            Evaluation::where('period_id', $period->id)->whereIn('criteria_id', $removed)->delete();
            AggregatedEvaluation::where('period_id', $period->id)->whereIn('criteria_id', $removed)->delete();
        }

        PairwiseComparison::where('period_id', $period->id)->delete();

        $period->linkedCriteria()->sync($syncData);

        CriteriaWeight::where('period_id', $period->id)->delete();
        foreach ($orderedIds as $cid) {
            CriteriaWeight::create([
                'period_id' => $period->id,
                'criteria_id' => $cid,
                'weight' => $weightsNormalized[$cid] ?? 0.0,
            ]);
        }

        $this->groupDecisionAggregator->clearAggregatesForPeriod((int) $period->id);
    }

    public function show(SelectionPeriod $period)
    {
        $period->load([
            'linkedCriteria',
            'applicants',
            'criteriaWeights.criteria',
            'selectionResults.applicant',
        ]);

        return view('BE.pages.periods.show', compact('period'));
    }

    public function edit(SelectionPeriod $period)
    {
        $period->load([
            'linkedCriteria' => static fn ($q) => $q->with('subCriteria'),
            'criteriaWeights',
            'evaluators',
        ]);

        $criteriaMaster = Criteria::active()->with('subCriteria')->orderBy('code')->get();
        $evaluators = Evaluator::active()->get();

        return view('BE.pages.periods.edit', compact('period', 'criteriaMaster', 'evaluators'));
    }

    public function update(Request $request, SelectionPeriod $period)
    {
        $validated = $this->validatePeriodForm($request, true);

        DB::transaction(function () use ($request, $period, $validated): void {
            $period->update(
                $request->only('name', 'position', 'start_date', 'end_date', 'description', 'status')
            );
            $this->syncPeriodCriteriaAndWeights($period, $validated['criteria_ids'], $validated['weights_normalized']);
            // Sync evaluators to period
            $period->evaluators()->sync($validated['evaluator_ids']);
        });

        return redirect()->route('periods.index')->with('success', 'Periode seleksi berhasil diperbarui.');
    }

    public function destroy(SelectionPeriod $period)
    {
        $period->delete();

        return redirect()->route('periods.index')->with('success', 'Periode seleksi berhasil dihapus.');
    }
}
