<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'Semua') {
            $query->where('status', $request->status);
        }

        $reports = $query->get();

        return view('reports.index', compact('reports'));
    }

    public function create()
    {
        return view('reports.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reporter_name' => 'required|string|max:100',
            'reporter_contact' => 'nullable|string|max:40',
            'infrastructure_type' => 'required|in:Jalan,Drainase',
            'title' => 'required|string|max:150',
            'description' => 'required|string|max:2000',
            'address' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'remaining_width' => 'nullable|numeric|min:0',
            'traffic_volume' => 'nullable|integer|min:0',
            'geometry' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('reports', 'public');
        }

        unset($validated['photo']);

        $validated['status'] = 'Menunggu';
        $validated['priority'] = 'Sedang';

        Report::create($validated);

        return redirect()->route('reports.index')
            ->with('success', 'Laporan berhasil dikirim.');
    }

    public function show(Report $report)
    {
        return view('reports.show', compact('report'));
    }

    public function updateStatus(Request $request, Report $report)
    {
        $validated = $request->validate([
            'status' => 'required|in:Menunggu,Diverifikasi,Diproses,Selesai',
            'priority' => 'required|in:Rendah,Sedang,Tinggi',
        ]);

        $report->update($validated);

        return redirect()->route('reports.show', $report)
            ->with('success', 'Status laporan berhasil diperbarui.');
    }

    public function uploadAfter(Request $request, Report $report)
    {
        $request->validate([
            'after_photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('after_photo')) {
            if ($report->after_photo_path) {
                Storage::disk('public')->delete($report->after_photo_path);
            }

            $path = $request->file('after_photo')->store('reports', 'public');
            $report->update(['after_photo_path' => $path]);
        }

        return redirect()->route('reports.show', $report)
            ->with('success', 'Foto bukti perbaikan berhasil diunggah.');
    }

    // FUNGSI BARU UNTUK MENGHITUNG MATERIAL (RAB)
    public function updateMaterial(Request $request, Report $report)
    {
        $validated = $request->validate([
            'damage_length' => 'required|numeric|min:0.1',
            'damage_width' => 'required|numeric|min:0.1',
            'damage_depth' => 'required|numeric|min:0.1',
        ]);

        $report->update($validated);

        return redirect()->route('reports.show', $report)
            ->with('success', 'Dimensi kerusakan berhasil disimpan. Kebutuhan aspal telah dihitung.');
    }
}
