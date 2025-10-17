<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang; // Pastikan model Barang sudah diimpor
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Pastikan relasi 'kategori' di-load
        $barang = Barang::with('kategori')->get();
        return response()->json($barang);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'kode_barang' => 'required|string|max:20|unique:barang',
                'nama_barang' => 'required|string|max:255',
                'id_kategori' => 'required|exists:kategori,id_kategori',
                // 'satuan' => 'nullable|string|max:50', // Jika satuan tetap ada tapi nullable
            ]);

            $barang = Barang::create([
                'kode_barang' => $request->kode_barang,
                'nama_barang' => $request->nama_barang,
                'id_kategori' => $request->id_kategori,
                'stok' => 0, // Stok awal selalu 0 saat menambah master produk baru
                // 'satuan' => $request->satuan, // Hapus atau biarkan jika nullable
            ]);

            return response()->json(['message' => 'Produk berhasil ditambahkan!', 'data' => $barang], 201);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Gagal menambahkan produk ke database.', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan server.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $barang = Barang::with('kategori')->find($id);
        if (!$barang) {
            return response()->json(['message' => 'Produk tidak ditemukan.'], 404);
        }
        return response()->json($barang);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $barang = Barang::find($id);
            if (!$barang) {
                return response()->json(['message' => 'Produk tidak ditemukan.'], 404);
            }

            $request->validate([
                'nama_barang' => 'required|string|max:255',
                'id_kategori' => 'required|exists:kategori,id_kategori',
                // 'satuan' => 'nullable|string|max:50', // Jika satuan tetap ada tapi nullable
            ]);

            $barang->update([
                'nama_barang' => $request->nama_barang,
                'id_kategori' => $request->id_kategori,
                // 'satuan' => $request->satuan, // Hapus atau biarkan jika nullable
            ]);

            return response()->json(['message' => 'Produk berhasil diperbarui!', 'data' => $barang]);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Gagal memperbarui produk di database.', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan server.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $barang = Barang::find($id);
            if (!$barang) {
                return response()->json(['message' => 'Produk tidak ditemukan.'], 404);
            }

            // Anda mungkin ingin menambahkan logika untuk memastikan stok barang 0 sebelum dihapus
            // if ($barang->stok > 0) {
            //     return response()->json(['message' => 'Tidak bisa menghapus produk yang memiliki stok lebih dari 0.']);
            // }

            $barang->delete();

            return response()->json(['message' => 'Produk berhasil dihapus!']);
        } catch (QueryException $e) {
            // Ini bisa terjadi jika ada foreign key constraint
            return response()->json(['message' => 'Gagal menghapus produk. Mungkin ada transaksi terkait.', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan server.', 'error' => $e->getMessage()], 500);
        }
    }
}