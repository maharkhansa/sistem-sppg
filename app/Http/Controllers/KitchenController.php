<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KitchenController extends Controller
{
    /**
     * Daftar data SPPG.
     */
    public function index()
    {
        $kitchens = Kitchen::latest()->get();

        return view('kitchens.index', compact('kitchens'));
    }

    /**
     * Form tambah SPPG.
     */
    public function create()
    {
        return view('kitchens.create');
    }

    /**
     * Simpan data SPPG baru.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            // Data SPPG
            'id_sppg' => [
                'required',
                'string',
                'max:30',
                'unique:kitchens,id_sppg',
            ],
            'name' => ['required', 'string', 'max:150'],
            'kabupaten_kota' => ['required', 'string', 'max:100'],
            'provinsi' => ['required', 'string', 'max:100'],

            // Alamat lengkap dapur SPPG
            'alamat' => ['required', 'string', 'max:1000'],

            // Mitra / Yayasan
            'foundation_name' => ['nullable', 'string', 'max:150'],

            // Pengawas Keuangan
            'finance_officer_name' => ['nullable', 'string', 'max:150'],
            'finance_officer_nik' => ['nullable', 'string', 'max:50'],

            // Kepala SPPG
            'head_sppg_name' => ['nullable', 'string', 'max:150'],
            'head_sppg_nip' => ['nullable', 'string', 'max:50'],

            // Perwakilan Mitra / Yayasan
            'foundation_rep_name' => ['nullable', 'string', 'max:150'],
            'foundation_rep_nik' => ['nullable', 'string', 'max:50'],
        ]);

        Kitchen::create($data);

        return redirect()
            ->route('kitchens.index')
            ->with('success', 'Data SPPG berhasil ditambahkan.');
    }

    /**
     * Form edit SPPG.
     */
    public function edit(Kitchen $kitchen)
    {
        return view('kitchens.edit', compact('kitchen'));
    }

    /**
     * Perbarui data SPPG.
     */
    public function update(Request $request, Kitchen $kitchen)
    {
        $data = $request->validate([
            // Data SPPG
            'id_sppg' => [
                'required',
                'string',
                'max:30',
                Rule::unique('kitchens', 'id_sppg')->ignore($kitchen->id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'kabupaten_kota' => ['required', 'string', 'max:100'],
            'provinsi' => ['required', 'string', 'max:100'],

            // Alamat lengkap dapur SPPG
            'alamat' => ['required', 'string', 'max:1000'],

            // Mitra / Yayasan
            'foundation_name' => ['nullable', 'string', 'max:150'],

            // Pengawas Keuangan
            'finance_officer_name' => ['nullable', 'string', 'max:150'],
            'finance_officer_nik' => ['nullable', 'string', 'max:50'],

            // Kepala SPPG
            'head_sppg_name' => ['nullable', 'string', 'max:150'],
            'head_sppg_nip' => ['nullable', 'string', 'max:50'],

            // Perwakilan Mitra / Yayasan
            'foundation_rep_name' => ['nullable', 'string', 'max:150'],
            'foundation_rep_nik' => ['nullable', 'string', 'max:50'],
        ]);

        $kitchen->update($data);

        return redirect()
            ->route('kitchens.index')
            ->with('success', 'Data SPPG berhasil diperbarui.');
    }

    /**
     * Aktifkan atau nonaktifkan SPPG.
     */
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