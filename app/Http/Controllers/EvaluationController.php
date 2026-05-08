<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Evaluation;
use App\Models\Evaluator;
use App\Models\SelectionPeriod;
use App\Services\GroupDecisionAggregator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    public function __construct(
        private readonly GroupDecisionAggregator $groupDecisionAggregator,
    ) {}

    public function index(Request $request)
    {
        $periods = SelectionPeriod::orderBy('name')->get();
        $periodId = $request->get('period_id');
        $applicants = collect();
        $criteria = collect();
        /** @var \Illuminate\Support\Collection<int, mixed>|\Illuminate\Support\Collection $evaluations */
        $evaluations = collect();
        $selectedPeriod = null;
        $evaluators = Evaluator::active()->get();

        /** @var Evaluator|null $activeEvaluatorContext */
        $activeEvaluatorContext = null;
        $evaluatorLocked = false;

        $requestEvaluatorId = $request->filled('evaluator_id') ? (int) $request->evaluator_id : null;

        if (Auth::user()->isAdmin()) {
            if ($periodId !== null && $evaluators->isNotEmpty()) {
                $chosenId = $requestEvaluatorId ?: $evaluators->first()->id;
                $activeEvaluatorContext = $evaluators->firstWhere('id', $chosenId) ?? $evaluators->first();
            }
            $evaluatorLocked = false;
        } elseif (Auth::user()->isEvaluatorUser()) {
            $activeEvaluatorContext = Auth::user()->evaluatorSeat;
            $evaluatorLocked = true;

            abort_unless(
                $activeEvaluatorContext !== null && $activeEvaluatorContext->is_active,
                403,
                'Akun Anda tidak tertaut evaluator aktif.'
            );

            if ($periodId !== null && $requestEvaluatorId !== null && $requestEvaluatorId !== $activeEvaluatorContext->id) {
                abort(403, 'Tidak boleh mengisi penilaian untuk evaluator lain.');
            }
        } else {
            abort(403, 'Hanya admin atau evaluator yang dapat mengisi penilaian.');
        }

        if ($periodId) {
            $selectedPeriod = SelectionPeriod::find($periodId);
            abort_unless($selectedPeriod !== null, 404);

            $criteria = $selectedPeriod->linkedCriteria()->with('subCriteria')->get();

            $evaluatorQueryId = $activeEvaluatorContext?->id;

            $applicants = Applicant::where('period_id', $periodId)->orderBy('name')->get();

            if ($evaluatorQueryId !== null && $evaluators->isNotEmpty()) {
                $evaluations = $applicants->mapWithKeys(function (Applicant $applicant) use ($periodId, $evaluatorQueryId) {
                    $rows = Evaluation::where('period_id', $periodId)
                        ->where('evaluator_id', $evaluatorQueryId)
                        ->where('applicant_id', $applicant->id)
                        ->get();

                    return [$applicant->id => $rows];
                });
            }
        }

        return view('BE.pages.evaluations.index', compact(
            'periods',
            'applicants',
            'criteria',
            'evaluations',
            'periodId',
            'selectedPeriod',
            'evaluators',
            'activeEvaluatorContext',
            'evaluatorLocked',
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:selection_periods,id',
            'evaluator_id' => 'nullable|exists:evaluators,id',
            'scores' => 'required|array',
            'scores.*.*' => 'required|numeric|min:1|max:5',
        ]);

        abort_unless(
            Auth::user()->isAdmin() || Auth::user()->evaluatorSeat,
            403
        );

        $periodId = (int) $request->period_id;

        if (Auth::user()->isAdmin()) {
            $evaluatorId = (int) $request->evaluator_id;
            abort_if($evaluatorId < 1, 422, 'Evaluator wajib dipilih oleh admin.');
        } else {
            $seat = Auth::user()->evaluatorSeat;
            abort_unless($seat && $seat->is_active, 403);
            $evaluatorId = $seat->id;
        }

        Evaluation::where('period_id', $periodId)
            ->where('evaluator_id', $evaluatorId)
            ->delete();

        $allowedCriteriaIds = SelectionPeriod::findOrFail($periodId)->linkedCriteria()->pluck('criteria.id')->map(static fn ($id): int => (int) $id)->unique()->sort()->values()->all();

        foreach ($request->scores as $applicantId => $criteriaScores) {
            foreach ($criteriaScores as $criteriaId => $score) {
                $criteriaIdInt = (int) $criteriaId;
                abort_unless(in_array($criteriaIdInt, $allowedCriteriaIds, true), 422, 'Skor tidak sesuai kriteria untuk periode ini.');
                Evaluation::create([
                    'period_id' => $periodId,
                    'applicant_id' => (int) $applicantId,
                    'criteria_id' => $criteriaIdInt,
                    'evaluator_id' => $evaluatorId,
                    'score' => $score,
                ]);
            }
        }

        $this->groupDecisionAggregator->clearAggregatesForPeriod($periodId);

        $redirectQuery = ['period_id' => $periodId];

        if (Auth::user()->isAdmin()) {
            $redirectQuery['evaluator_id'] = $evaluatorId;
        }

        return redirect()->route('evaluations.index', $redirectQuery)
            ->with('success', 'Penilaian berhasil disimpan. Jalankan ulang agregasi KMKK setelah seluruh evaluator selesai, lalu lakukan TOPSIS.');
    }
}
