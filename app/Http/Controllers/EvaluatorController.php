<?php

namespace App\Http\Controllers;

use App\Models\Evaluator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EvaluatorController extends Controller
{
    public function index()
    {
        $evaluators = Evaluator::with('user')->orderBy('sort_order')->paginate(20);

        return view('BE.pages.evaluators.index', compact('evaluators'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get(['id', 'name', 'email', 'role']);

        return view('BE.pages.evaluators.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['nullable', 'string', 'max:48', 'unique:evaluators,code'],
            'name' => ['required', 'string', 'max:255'],
            'role_label' => ['nullable', 'string', 'max:96'],
            'user_id' => ['nullable', 'exists:users,id', Rule::unique('evaluators', 'user_id')],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Evaluator::create([
            'code' => $request->input('code'),
            'name' => $request->name,
            'role_label' => $request->role_label,
            'user_id' => $request->user_id ?: null,
            'sort_order' => (int) $request->get('sort_order', 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('evaluators.index')->with('success', 'Evaluator ditambahkan.');
    }

    public function edit(Evaluator $evaluator)
    {
        $users = User::orderBy('name')->get(['id', 'name', 'email', 'role']);

        return view('BE.pages.evaluators.edit', compact('evaluator', 'users'));
    }

    public function update(Request $request, Evaluator $evaluator)
    {
        $request->validate([
            'code' => ['nullable', 'string', 'max:48', Rule::unique('evaluators', 'code')->ignore($evaluator->id)],
            'name' => ['required', 'string', 'max:255'],
            'role_label' => ['nullable', 'string', 'max:96'],
            'user_id' => ['nullable', 'exists:users,id', Rule::unique('evaluators', 'user_id')->ignore($evaluator->id)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $evaluator->update([
            'code' => $request->input('code'),
            'name' => $request->name,
            'role_label' => $request->role_label,
            'user_id' => $request->user_id ?: null,
            'sort_order' => (int) $request->get('sort_order', $evaluator->sort_order),
            'is_active' => $request->boolean('is_active', $evaluator->is_active),
        ]);

        return redirect()->route('evaluators.index')->with('success', 'Evaluator diperbarui.');
    }

    public function destroy(Evaluator $evaluator)
    {
        abort_if($evaluator->evaluations()->exists(), 403, 'Evaluator masih punya data penilaian; tidak bisa dihapus.');

        $evaluator->delete();

        return redirect()->route('evaluators.index')->with('success', 'Evaluator dihapus.');
    }
}
