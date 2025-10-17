<?php
// app/Models/TransaksiMasuk.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiMasuk extends Model
{
    use HasFactory;
    
    protected $table = 'tbl_transaksi_masuk';
    protected $primaryKey = 'id_transaksi_masuk';

     protected $fillable = [
        'id_barang', 
        'kode_barang', 
        'id_kategori', 
        'nama_barang', 
        'nominal', 
        'harga', 
        'tanggal_transaksi', 
        'keterangan',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }
}