<?php
// app/Http/Controllers/Api/TransaksiKeluarController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\TransaksiKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TransaksiKeluarController extends Controller
{
    /**
     * READ: Menampilkan semua data Transaksi Keluar. (Route: GET /api/transaksi-keluar)
     */
    public function index()
    {
        try {
            // Mengambil transaksi keluar dengan detail Barang dan User
            $transaksiKeluar = TransaksiKeluar::with(['barang.kategori', 'user'])
                ->orderBy('tanggal_keluar', 'desc')
                ->get();
                
            return response()->json($transaksiKeluar, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data transaksi keluar.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * CREATE/STORE: Menyimpan transaksi keluar dan MENGURANGI Stok Barang. (Route: POST /api/transaksi-keluar)
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'id_barang' => 'required|exists:tbl_barang,id_barang',
            'id_user' => 'required|exists:tbl_user,id_user', // ID User yang mencatat
            'jumlah' => 'required|integer|min:1', // QTY keluar
            'harga_satuan_jual' => 'required|numeric|min:0', // Harga jual satuan
            'tujuan' => 'required|string|max:150',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $idBarang = $validated['id_barang'];
        $jumlahKeluar = $validated['jumlah'];
        
        DB::beginTransaction(); // Memulai transaksi database

        try {
            // 2. Cek Stok
            $barang = Barang::findOrFail($idBarang);

            if ($barang->stok < $jumlahKeluar) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Gagal mencatat transaksi. Stok tidak mencukupi.',
                    'stok_saat_ini' => $barang->stok
                ], 400); // Bad Request
            }

            // 3. Update Stok (Kurangi)
            $barang->stok -= $jumlahKeluar;
            
            // Update status barang
            if ($barang->stok <= 0) {
                 $barang->status_barang = '?Take/Sale'; // Sesuai ENUM skema Anda
            } else {
                 $barang->status_barang = '?Ready';
            }
            
            $barang->save();

            // 4. Catat Transaksi Keluar
            $transaksiKeluar = TransaksiKeluar::create([
                'id_barang' => $idBarang,
                'id_user' => $validated['id_user'],
                // tanggal_keluar otomatis terisi oleh timestamp()
                'jumlah' => $jumlahKeluar,
                'harga_satuan_jual' => $validated['harga_satuan_jual'],
                'tujuan' => $validated['tujuan'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            DB::commit(); // Konfirmasi perubahan

            return response()->json([
                'message' => 'Transaksi keluar berhasil dicatat dan stok dikurangi.',
                'data' => $transaksiKeluar
            ], 201);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Barang tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            DB::rollBack(); 
            return response()->json([
                'message' => 'Gagal mencatat transaksi keluar dan mengupdate stok.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}