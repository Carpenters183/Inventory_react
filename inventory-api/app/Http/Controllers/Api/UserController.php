<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * READ: Menampilkan semua data pengguna.
     * Endpoint: GET /api/user
     */
    public function index()
    {
        try {
            $users = User::select('id_user', 'username', 'nama_lengkap', 'role', 'last_login')
                ->orderBy('id_user', 'asc')
                ->get();
                
            // Format data: ubah 'id_user' menjadi 'id' untuk kompatibilitas frontend
            $formattedUsers = $users->map(function ($user) {
                $user->id = $user->id_user;
                unset($user->id_user);
                return $user->only(['id', 'username', 'nama_lengkap', 'role', 'last_login']);
            });

            return response()->json($formattedUsers, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil data pengguna.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * CREATE: Menyimpan pengguna baru.
     * Endpoint: POST /api/user
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                // Perbaikan: unique merujuk ke tabel yang benar (tbl_user)
                'username' => 'required|string|max:255|unique:tbl_user,username',
                
                'password' => 'required|string|min:6',
                'nama_lengkap' => 'required|string|max:255',
                'role' => 'required|string|in:admin,kasir,gudang', 
                // Perbaikan: last_login dihilangkan dari validasi
            ]);

            $user = User::create([
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']), 
                'nama_lengkap' => $validated['nama_lengkap'],
                'role' => $validated['role'],
                'last_login' => null, // Diset null secara eksplisit
            ]);

            // Format response
            return response()->json([
                'message' => 'Pengguna berhasil dibuat.',
                'data' => [
                    'id' => $user->id_user,
                    'username' => $user->username,
                    'nama_lengkap' => $user->nama_lengkap,
                    'role' => $user->role,
                ]
            ], 201);
            
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal membuat pengguna.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * READ SINGLE: Menampilkan satu data pengguna berdasarkan ID.
     * Endpoint: GET /api/user/{id}
     */
    public function show($id_user)
    {
        try {
            $user = User::findOrFail($id_user); 
            $formattedUser = $user->only(['id_user', 'username', 'nama_lengkap', 'role', 'last_login']);
            $formattedUser['id'] = $formattedUser['id_user'];
            unset($formattedUser['id_user']);
            return response()->json($formattedUser, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil data pengguna.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * UPDATE: Memperbarui data pengguna.
     * Endpoint: PUT/PATCH /api/user/{id}
     */
    public function update(Request $request, $id_user)
    {
        try {
            $user = User::findOrFail($id_user);
            
            $validated = $request->validate([
                'username' => [
                    'sometimes', 'string', 'max:255',
                    // Perbaikan: Rule::unique menggunakan primary key 'id_user' di tabel 'tbl_user'
                    Rule::unique('tbl_user', 'username')->ignore($id_user, 'id_user'), 
                ],
                'password' => 'nullable|string|min:6',
                'nama_lengkap' => 'sometimes|string|max:255',
                'role' => 'sometimes|string|in:admin,kasir,gudang',
                'last_login' => 'nullable|date_format:Y-m-d H:i:s',
            ]);

            if (isset($validated['password']) && !empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']); 
            }

            $user->update($validated);

            return response()->json(['message' => 'Pengguna berhasil diperbarui.'], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui pengguna.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE: Menghapus data pengguna.
     * Endpoint: DELETE /api/user/{id}
     */
    public function destroy($id_user)
    {
        try {
            $user = User::findOrFail($id_user);
            $user->delete();
            
            return response()->json(['message' => 'Pengguna berhasil dihapus.'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus pengguna.', 'error' => $e->getMessage()], 500);
        }
    }
}