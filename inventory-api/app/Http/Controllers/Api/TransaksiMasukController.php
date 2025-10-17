<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang; // Memanggil Model Barang
use App\Models\TransaksiMasuk; // Memanggil Model TransaksiMasuk
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException; 
use Illuminate\Validation\ValidationException; 

class TransaksiMasukController extends Controller
{
    /**
     * READ: Menampilkan semua data Transaksi Masuk dengan relasi Barang.
     */
    public function index()
    {
        try {
            // Memuat relasi 'barang' dan 'kategori' untuk menampilkan detail transaksi
            $transaksiMasuk = TransaksiMasuk::with(['barang.kategori'])
                ->orderBy('tanggal_transaksi', 'desc')
                ->get();
                
            return response()->json($transaksiMasuk, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data transaksi masuk.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //-------------------------------------------------------------

    /**
     * CREATE/STORE: Menyimpan transaksi masuk untuk Barang yang sudah ada.
     * Alur: Cari Barang -> Update Stok -> Catat Transaksi Masuk.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input (Minimalis: Hanya data yang dibutuhkan untuk transaksi)
        try {
            $validated = $request->validate([
                'kode_barang' => 'required|max:50',
                'nominal' => 'required|integer|min:1', // Jumlah barang masuk
                'harga' => 'required|numeric|min:0', // Harga beli satuan (modal)
                'tanggal_transaksi' => 'required|date',
                'keterangan' => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal.', 'errors' => $e->errors()], 422);
        }

        $kodeBarang = $validated['kode_barang'];
        $nominalMasuk = $validated['nominal'];
        
        DB::beginTransaction(); 

        try {
            // 2. Cari Barang berdasarkan kode_barang
            $barang = Barang::where('kode_barang', $kodeBarang)->first();
            
            if (!$barang) {
                // Barang tidak ditemukan, batalkan transaksi.
                DB::rollBack();
                return response()->json([
                    'message' => 'Barang tidak ditemukan.',
                    'hint' => 'Kode barang ini belum terdaftar. Silakan buat data Barang terlebih dahulu.'
                ], 404);
            }
            
            // 3. UPDATE Stok di tbl_barang
            $barang->stok += $nominalMasuk;
            $barang->tanggal_masuk_terakhir = $validated['tanggal_transaksi'];
            $barang->save();
            
            // 4. Catat Transaksi Masuk
            TransaksiMasuk::create([
                'id_barang' => $barang->id_barang, 
                // Mengambil data statis dari barang yang sudah ada
                'kode_barang' => $barang->kode_barang, 
                'id_kategori' => $barang->id_kategori, 
                'nama_barang' => $barang->nama_barang, 
                
                // Data dari form transaksi
                'nominal' => $nominalMasuk,
                'harga' => $validated['harga'],
                'tanggal_transaksi' => $validated['tanggal_transaksi'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            DB::commit(); 
            
            return response()->json(['message' => 'Transaksi masuk berhasil dicatat.'], 201);

        } catch (QueryException $e) {
             DB::rollBack();
             // Mengembalikan detail error SQL untuk debugging database
             return response()->json([
                 'message' => 'Gagal mencatat transaksi. Kesalahan database.', 
                 'detail_sql_error' => $e->getMessage(),
                 'hint' => 'Periksa kolom NOT NULL yang mungkin kosong pada tabel Transaksi Masuk atau masalah Foreign Key.'
             ], 500);
        } catch (\Exception $e) {
            DB::rollBack(); 
            return response()->json(['message' => 'Terjadi kesalahan tak terduga.', 'error' => $e->getMessage()], 500);
        }
    }
}