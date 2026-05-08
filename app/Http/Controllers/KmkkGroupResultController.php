<?php

namespace App\Http\Controllers;

use App\Models\AggregatedEvaluation;
use App\Models\Applicant;
use App\Models\Evaluation;
use App\Models\Evaluator;
use App\Models\SelectionPeriod;
use App\Services\GroupDecisionAggregator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KmkkGroupResultController extends Controller
{
    public function __construct(
        private readonly GroupDecisionAggregator $aggregator,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless(
            $user->isAdmin() || $user->isDirektur() || $user->isEvaluatorUser(),
            403,
            'Akses ditolak.'
        );

        $periods = SelectionPeriod::orderBy('name')->get();
        $periodId = $request->integer('period_id') ?: null;
        $focusApplicantId = $request->integer('applicant_id') ?: null;
        $evaluators = Evaluator::active()->get();

        $selectedPeriod = null;
        $criteria = collect();
        $applicants = collect();

        /** @var \Illuminate\Support\Collection<int,Evaluation>|\Illuminate\Support\Collection $rawEvaluations */
        $rawEvaluations = collect();
        /** @var \Illuminate\Support\Collection<int, mixed>|\Illuminate\Support\Collection $aggregatesByApplicant */
        $aggregatesByApplicant = collect();
        $aggregateComplete = false;

        /** Total baris penilaian mentah dalam periode (tanpa filter, untuk konteks badge). */
        $rawEvaluationsTotal = 0;

        if ($periodId) {
            $selectedPeriod = SelectionPeriod::find($periodId);

            if ($selectedPeriod !== null) {
                $criteria = $selectedPeriod->linkedCriteria()->with('subCriteria')->orderByPivot('sort_order')->get();
                $applicants = Applicant::where('period_id', $periodId)->orderBy('name')->get();

                if ($focusApplicantId && ! $applicants->contains('id', $focusApplicantId)) {
                    $focusApplicantId = null;
                }

                $rawEvaluationsTotal = Evaluation::where('period_id', $periodId)->count();

                if ($focusApplicantId !== null) {
                    $rawEvaluations = Evaluation::where('period_id', $periodId)
                        ->where('applicant_id', $focusApplicantId)
                        ->with(['evaluator', 'criteria', 'applicant'])
                        ->orderBy('criteria_id')
                        ->orderBy('evaluator_id')
                        ->get();
                } else {
                    $rawEvaluations = collect();
                }

                $aggregatesByApplicant = AggregatedEvaluation::where('period_id', $periodId)
                    ->with(['criteria'])
                    ->get()
                    ->groupBy('applicant_id');

                $aggregateComplete = $this->aggregator->isAggregateMatrixComplete($periodId);
            }
        }

        return view('BE.pages.kmkk.group-results', compact(
            'periods',
            'periodId',
            'evaluators',
            'selectedPeriod',
            'criteria',
            'applicants',
            'focusApplicantId',
            'rawEvaluationsTotal',
            'rawEvaluations',
            'aggregatesByApplicant',
            'aggregateComplete',
        ));
    }

    public function rebuild(Request $request): \Illuminate\Http\RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403, 'Hanya administrator yang dapat membangun agregasi.');

        $request->validate([
            'period_id' => ['required', 'exists:selection_periods,id'],
            'aggregation_method' => ['nullable', 'in:average,owa_most'],
            'owa_alpha' => ['nullable', 'numeric', 'min:1.01', 'max:99'],
        ]);

        $payload = $this->aggregator->rebuild(
            (int) $request->period_id,
            $request->input('aggregation_method'),
            $request->filled('owa_alpha') ? (float) $request->owa_alpha : null,
        );

        if (isset($payload['error'])) {
            return back()->with('error', $payload['error']);
        }

        return back()->with('success', sprintf(
            'Matriks agregat KMKK berhasil dibuat (%d sel, metode: %s). Anda dapat melanjutkan ke TOPSIS.',
            $payload['cells'],
            $payload['method'] === 'owa_most' ? 'OWA (Yager linguistik)' : 'Rata-rata aritmetik'
        ));
    }
}
