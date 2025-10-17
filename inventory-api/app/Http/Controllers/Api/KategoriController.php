<?php
// app/Http/Controllers/Api/KategoriController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori; // Pastikan Model Kategori sudah Anda buat
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class KategoriController extends Controller
{
    /**
     * READ: Menampilkan semua data kategori. (Route: GET /api/kategori)
     */
    public function index() 
    { 
        try {
            // Mengambil semua data kategori
            $kategori = Kategori::all();
            return response()->json($kategori, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data kategori.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * CREATE: Menyimpan data kategori baru. (Route: POST /api/kategori)
     */
    public function store(Request $request)
    {
        // Validasi, pastikan nama_kategori unik
        $validated = $request->validate([
            'nama_kategori' => 'required|unique:tbl_kategori,nama_kategori|max:100',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        try {
            $kategori = Kategori::create($validated);
            return response()->json([
                'message' => 'Kategori berhasil ditambahkan.',
                'data' => $kategori
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menyimpan kategori.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * UPDATE: Memperbarui data kategori berdasarkan ID. (Route: PUT/PATCH /api/kategori/{id})
     */
    public function update(Request $request, $id)
    {
        try {
            $kategori = Kategori::findOrFail($id);

            // Validasi: nama_kategori harus unik, kecuali untuk kategori ini sendiri
            $validated = $request->validate([
                'nama_kategori' => [
                    'required',
                    Rule::unique('tbl_kategori', 'nama_kategori')->ignore($kategori->id_kategori, 'id_kategori'),
                    'max:100'
                ],
                'deskripsi' => 'nullable|string|max:255',
            ]);

            $kategori->update($validated);

            return response()->json([
                'message' => 'Kategori berhasil diperbarui.',
                'data' => $kategori
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Kategori tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui kategori.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE: Menghapus data kategori berdasarkan ID. (Route: DELETE /api/kategori/{id})
     */
    public function destroy($id)
    {
        try {
            $kategori = Kategori::findOrFail($id);
            $kategori->delete();

            return response()->json(['message' => 'Kategori berhasil dihapus.'], 200);
            
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Kategori tidak ditemukan.'], 404);
        } catch (\Exception $e) {
             // Beri pesan yang jelas jika ada barang yang masih menggunakan kategori ini
             if (str_contains($e->getMessage(), 'foreign key constraint')) {
                return response()->json(['message' => 'Gagal menghapus kategori. Kategori ini masih digunakan oleh data Barang.'], 409); // Conflict
            }
            return response()->json([
                'message' => 'Gagal menghapus kategori.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}