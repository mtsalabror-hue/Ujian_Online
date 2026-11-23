<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class PenggunaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $keyword = $request->keyword;
        $role = $request->role;

        $users = User::when($keyword, function ($query) use ($keyword) {

            $query->where('username', 'like', "%{$keyword}%")
                ->orWhere('role', 'like', "%{$keyword}%");
        })
            ->when($role, function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->orderBy('role')
            ->orderBy('username')
            ->paginate(10);

        return view('admin.pengguna.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pengguna.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:100',
                'unique:users,username',
            ],

            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
            ],

            'role' => [
                'required',
                Rule::in(['admin', 'guru', 'siswa']),
            ],

            'status' => [
                'required',
                'boolean',
            ],
        ]);

        User::create([
            'username' => $validated['username'],

            'password' => Hash::make(
                $validated['password']
            ),

            'role' => $validated['role'],

            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.pengguna.index')
            ->with(
                'success',
                'Pengguna berhasil ditambahkan.'
            );
    }

    /**
     * Display the specified resource.
     */
    public function show(User $pengguna)
    {
        return view('admin.pengguna.show', [
            'user' => $pengguna
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $pengguna)
    {
        return view('admin.pengguna.edit', [
            'user' => $pengguna
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $pengguna)
    {
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:100',

                Rule::unique('users', 'username')
                    ->ignore($pengguna->id),
            ],

            'role' => [
                'required',
                Rule::in(['admin', 'guru', 'siswa']),
            ],

            'status' => [
                'required',
                'boolean',
            ],

            'password' => [
                'nullable',
                'string',
                'min:6',
            ],
        ]);

        $data = [
            'username' => $validated['username'],
            'role'     => $validated['role'],
            'status'   => $validated['status'],
        ];

        // Password hanya diubah jika diisi
        if (!empty($validated['password'])) {

            $data['password'] = Hash::make(
                $validated['password']
            );
        }

        $pengguna->update($data);

        return redirect()
            ->route('admin.pengguna.index')
            ->with(
                'success',
                'Data pengguna berhasil diperbarui.'
            );
    }

    /**
     * Tampilkan form reset password manual oleh admin.
     */
    public function resetPassword(User $pengguna)
    {
        if ($pengguna->role === 'siswa') {

            return redirect()
                ->route('admin.pengguna.index')
                ->with(
                    'error',
                    'Reset password tidak tersedia untuk akun siswa. Hubungi admin untuk bantuan.'
                );
        }

        return view('admin.pengguna.reset-password', [
            'user' => $pengguna,
        ]);
    }

    /**
     * Simpan password baru hasil reset manual oleh admin.
     */
    public function resetPasswordStore(Request $request, User $pengguna)
    {
        if ($pengguna->role === 'siswa') {

            return redirect()
                ->route('admin.pengguna.index')
                ->with(
                    'error',
                    'Reset password tidak tersedia untuk akun siswa. Hubungi admin untuk bantuan.'
                );
        }

        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
            ],
        ]);

        $pengguna->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('admin.pengguna.index')
            ->with(
                'success',
                "Password akun \"{$pengguna->username}\" berhasil direset."
            );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $pengguna)
    {
        if (auth()->id() === $pengguna->id) {

            return redirect()
                ->route('admin.pengguna.index')
                ->with(
                    'error',
                    'Anda tidak dapat menghapus akun yang sedang digunakan.'
                );
        }
        if (in_array($pengguna->role, ['guru', 'siswa'])) {

            return redirect()
                ->route('admin.pengguna.index')
                ->with(
                    'error',
                    'Akun Guru atau Siswa tidak dapat dihapus dari Kelola Pengguna. Hapus melalui Data Master terkait.'
                );
        }

        $pengguna->delete();

        return redirect()
            ->route('admin.pengguna.index')
            ->with(
                'success',
                'Pengguna berhasil dihapus.'
            );
    }
}
