<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 🔥 PASTIKAN NAMA TABEL ADALAH 'tbl_barang'
        Schema::table('tbl_barang', function (Blueprint $table) { 
            $table->dropColumn('satuan'); // Hapus kolom satuan
        });
    }

    public function down(): void
    {
        Schema::table('tbl_barang', function (Blueprint $table) {
            // Tambahkan kembali kolom satuan jika perlu rollback
            $table->string('satuan', 50)->nullable(); 
        });
    }
};