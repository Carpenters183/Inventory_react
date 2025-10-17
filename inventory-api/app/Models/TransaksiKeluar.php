<?php
// app/Models/TransaksiKeluar.php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiKeluar extends Model
{
    use HasFactory;
    protected $table = 'tbl_transaksi_keluar';
    protected $primaryKey = 'id_transaksi_keluar';
    public $timestamps = false;
    protected $fillable = [
        'id_barang', 'id_user', 'tanggal_keluar', 'jumlah', 
        'harga_satuan_jual', 'tujuan', 'keterangan'
    ];
}