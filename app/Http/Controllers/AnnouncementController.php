<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\SelectionPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with(['period', 'creator'])->latest()->get();
        return view('BE.pages.announcements.index', compact('announcements'));
    }

    public function create()
    {
        $periods = SelectionPeriod::orderBy('name')->get();
        return view('BE.pages.announcements.create', compact('periods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'period_id' => 'nullable|exists:selection_periods,id',
            'is_published' => 'boolean',
        ]);

        Announcement::create([
            ...$request->only('title', 'content', 'period_id'),
            'is_published' => $request->boolean('is_published'),
            'published_at' => $request->boolean('is_published') ? now() : null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function edit(Announcement $announcement)
    {
        $periods = SelectionPeriod::orderBy('name')->get();
        return view('BE.pages.announcements.edit', compact('announcement', 'periods'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'period_id' => 'nullable|exists:selection_periods,id',
            'is_published' => 'boolean',
        ]);

        $announcement->update([
            ...$request->only('title', 'content', 'period_id'),
            'is_published' => $request->boolean('is_published'),
            'published_at' => $request->boolean('is_published') && !$announcement->published_at ? now() : $announcement->published_at,
        ]);

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil dihapus.');
    }
}
