<?php

namespace App\Http\Controllers;

use App\Models\Criteria;
use App\Models\SubCriteria;
use Illuminate\Http\Request;

class CriteriaController extends Controller
{
    public function index()
    {
        $criteria = Criteria::with('subCriteria')->orderBy('code')->get();
        return view('BE.pages.criteria.index', compact('criteria'));
    }

    public function create()
    {
        $lastCode = Criteria::orderBy('code', 'desc')->first();
        $nextNumber = $lastCode ? ((int) substr($lastCode->code, 1)) + 1 : 1;
        $suggestedCode = 'C' . $nextNumber;
        return view('BE.pages.criteria.create', compact('suggestedCode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:criteria,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:benefit,cost',
            'importance' => 'required|integer|min:1|max:9',
            'description' => 'nullable|string',
        ]);

        Criteria::create($request->only('code', 'name', 'type', 'importance', 'description'));

        return redirect()->route('criteria.index')->with('success', 'Kriteria berhasil ditambahkan.');
    }

    public function edit(Criteria $criteria)
    {
        $criteria->load('subCriteria');
        return view('BE.pages.criteria.edit', compact('criteria'));
    }

    public function update(Request $request, Criteria $criteria)
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:criteria,code,' . $criteria->id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:benefit,cost',
            'importance' => 'required|integer|min:1|max:9',
            'description' => 'nullable|string',
        ]);

        $criteria->update($request->only('code', 'name', 'type', 'importance', 'description'));

        return redirect()->route('criteria.index')->with('success', 'Kriteria berhasil diperbarui.');
    }

    public function destroy(Criteria $criteria)
    {
        $criteria->delete();
        return redirect()->route('criteria.index')->with('success', 'Kriteria berhasil dihapus.');
    }

    public function toggleActive(Criteria $criteria)
    {
        $criteria->update(['is_active' => !$criteria->is_active]);
        return back()->with('success', 'Status kriteria berhasil diubah.');
    }

    public function storeSubCriteria(Request $request, Criteria $criteria)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|integer|min:1|max:10',
            'description' => 'nullable|string',
        ]);

        $criteria->subCriteria()->create($request->only('name', 'value', 'description'));

        return back()->with('success', 'Sub kriteria berhasil ditambahkan.');
    }

    public function destroySubCriteria(SubCriteria $subCriteria)
    {
        $subCriteria->delete();
        return back()->with('success', 'Sub kriteria berhasil dihapus.');
    }
}
