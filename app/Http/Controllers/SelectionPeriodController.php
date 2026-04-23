<?php

namespace App\Http\Controllers;

use App\Models\SelectionPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SelectionPeriodController extends Controller
{
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
        return view('BE.pages.periods.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'description' => 'nullable|string',
        ]);

        SelectionPeriod::create([
            ...$request->only('name', 'position', 'start_date', 'end_date', 'description'),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('periods.index')->with('success', 'Periode seleksi berhasil dibuat.');
    }

    public function show(SelectionPeriod $period)
    {
        $period->load(['applicants', 'criteriaWeights.criteria', 'selectionResults.applicant']);
        return view('BE.pages.periods.show', compact('period'));
    }

    public function edit(SelectionPeriod $period)
    {
        return view('BE.pages.periods.edit', compact('period'));
    }

    public function update(Request $request, SelectionPeriod $period)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,open,closed,completed',
        ]);

        $period->update($request->only('name', 'position', 'start_date', 'end_date', 'description', 'status'));

        return redirect()->route('periods.index')->with('success', 'Periode seleksi berhasil diperbarui.');
    }

    public function destroy(SelectionPeriod $period)
    {
        $period->delete();
        return redirect()->route('periods.index')->with('success', 'Periode seleksi berhasil dihapus.');
    }
}
