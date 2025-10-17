<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'tbl_barang';
    protected $primaryKey = 'id_barang';
    public $timestamps = false; // Karena tabel Anda tidak menggunakan created_at/updated_at

 protected $fillable = [
        'kode_barang',
        'nama_barang',
        'id_kategori',
        'stok'
        // Tidak ada field harga di sini.
    ];
    
    // Definisikan relasi ke Kategori jika ada
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }
}