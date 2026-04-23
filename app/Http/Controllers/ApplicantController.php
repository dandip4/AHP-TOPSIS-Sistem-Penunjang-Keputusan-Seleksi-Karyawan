<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\SelectionPeriod;
use Illuminate\Http\Request;

class ApplicantController extends Controller
{
    public function index(Request $request)
    {
        $periods = SelectionPeriod::orderBy('name')->get();
        $query = Applicant::with('period');

        if ($request->filled('period_id')) {
            $query->where('period_id', $request->period_id);
        }

        $applicants = $query->latest()->get();
        return view('BE.pages.applicants.index', compact('applicants', 'periods'));
    }

    public function create()
    {
        $periods = SelectionPeriod::whereIn('status', ['draft', 'open'])->get();
        return view('BE.pages.applicants.create', compact('periods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:selection_periods,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|in:L,P',
            'birth_date' => 'nullable|date',
            'education' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'gpa' => 'nullable|numeric|min:0|max:4',
            'age' => 'nullable|integer|min:17|max:60',
            'address' => 'nullable|string',
        ]);

        Applicant::create($request->only([
            'period_id', 'name', 'email', 'phone', 'gender',
            'birth_date', 'education', 'major', 'gpa', 'age', 'address',
        ]));

        return redirect()->route('applicants.index', ['period_id' => $request->period_id])
            ->with('success', 'Pelamar berhasil ditambahkan.');
    }

    public function edit(Applicant $applicant)
    {
        $periods = SelectionPeriod::whereIn('status', ['draft', 'open'])->get();
        return view('BE.pages.applicants.edit', compact('applicant', 'periods'));
    }

    public function update(Request $request, Applicant $applicant)
    {
        $request->validate([
            'period_id' => 'required|exists:selection_periods,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|in:L,P',
            'birth_date' => 'nullable|date',
            'education' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'gpa' => 'nullable|numeric|min:0|max:4',
            'age' => 'nullable|integer|min:17|max:60',
            'address' => 'nullable|string',
        ]);

        $applicant->update($request->only([
            'period_id', 'name', 'email', 'phone', 'gender',
            'birth_date', 'education', 'major', 'gpa', 'age', 'address',
        ]));

        return redirect()->route('applicants.index', ['period_id' => $applicant->period_id])
            ->with('success', 'Data pelamar berhasil diperbarui.');
    }

    public function destroy(Applicant $applicant)
    {
        $periodId = $applicant->period_id;
        $applicant->delete();
        return redirect()->route('applicants.index', ['period_id' => $periodId])
            ->with('success', 'Pelamar berhasil dihapus.');
    }
}
