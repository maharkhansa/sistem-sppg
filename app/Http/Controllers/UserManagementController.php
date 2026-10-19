<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    private function authorizeAdmin(): void
    {
        abort_unless(
            Auth::check() && strtoupper((string) Auth::user()->role) === 'ADMIN',
            403,
            'Anda tidak memiliki izin untuk mengelola akun.'
        );
    }

    public function index()
    {
        $this->authorizeAdmin();

        $users = User::query()
            ->orderBy('name')
            ->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $this->authorizeAdmin();

        return view('users.create');
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                'unique:users,username',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'name.required' => 'Nama admin wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'ADMIN',
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Akun admin berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $this->authorizeAdmin();

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'name.required' => 'Nama admin wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'username.unique' => 'Username sudah digunakan akun lain.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->email = $validated['email'];

        // Password hanya diperbarui jika kolom diisi.
        // Cast "hashed" pada model User akan meng-hash password.
        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success', 'Akun admin berhasil diperbarui.');
    }
}