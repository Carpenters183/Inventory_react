<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'tbl_user'; 
    protected $primaryKey = 'id_user';

    // 🔥 PERBAIKAN: Menonaktifkan fitur timestamp (created_at/updated_at)
    // Ini memperbaiki Error 500 jika tabel Anda tidak memiliki kolom tersebut.
    public $timestamps = false; 

    protected $fillable = [
        'username',
        'password',
        'nama_lengkap',
        'role',
        'last_login', // Ditambahkan ke fillable agar bisa diisi (walau nilainya null)
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'last_login' => 'datetime', 
    ];
}