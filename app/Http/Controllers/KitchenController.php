<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    public function index()
    {
        $kitchens = Kitchen::latest()->get();

        return view('kitchens.index', compact('kitchens'));
    }

    public function create()
    {
        return view('kitchens.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // Data SPPG
            'id_sppg' => 'required|string|max:30|unique:kitchens,id_sppg',
            'name' => 'required|string|max:150',
            'kabupaten_kota' => 'required|string|max:100',
            'provinsi' => 'required|string|max:100',

            // Mitra / Yayasan
            'foundation_name' => 'nullable|string|max:150',

            // Pengawas Keuangan
            'finance_officer_name' => 'nullable|string|max:150',
            'finance_officer_nik' => 'nullable|string|max:50',

            // Kepala SPPG
            'head_sppg_name' => 'nullable|string|max:150',
            'head_sppg_nip' => 'nullable|string|max:50',

            // Perwakilan Mitra / Yayasan
            'foundation_rep_name' => 'nullable|string|max:150',
            'foundation_rep_nik' => 'nullable|string|max:50',
        ]);

        Kitchen::create($data);

        return redirect()
            ->route('kitchens.index')
            ->with('success', 'Data SPPG berhasil ditambahkan.');
    }

    public function edit(Kitchen $kitchen)
    {
        return view('kitchens.edit', compact('kitchen'));
    }

    public function update(Request $request, Kitchen $kitchen)
    {
        $data = $request->validate([
            // Data SPPG
            'id_sppg' => 'required|string|max:30|unique:kitchens,id_sppg,' . $kitchen->id,
            'name' => 'required|string|max:150',
            'kabupaten_kota' => 'required|string|max:100',
            'provinsi' => 'required|string|max:100',

            // Mitra / Yayasan
            'foundation_name' => 'nullable|string|max:150',

            // Pengawas Keuangan
            'finance_officer_name' => 'nullable|string|max:150',
            'finance_officer_nik' => 'nullable|string|max:50',

            // Kepala SPPG
            'head_sppg_name' => 'nullable|string|max:150',
            'head_sppg_nip' => 'nullable|string|max:50',

            // Perwakilan Mitra / Yayasan
            'foundation_rep_name' => 'nullable|string|max:150',
            'foundation_rep_nik' => 'nullable|string|max:50',
        ]);

        $kitchen->update($data);

        return redirect()
            ->route('kitchens.index')
            ->with('success', 'Data SPPG berhasil diperbarui.');
    }

    public function toggleStatus(Kitchen $kitchen)
    {
        $kitchen->update([
            'status' => !$kitchen->status,
        ]);

        return redirect()
            ->route('kitchens.index')
            ->with('success', 'Status SPPG berhasil diperbarui.');
    }
}