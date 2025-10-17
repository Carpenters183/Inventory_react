<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TransaksiMasukController;
use App\Http\Controllers\Api\TransaksiKeluarController;
use App\Http\Controllers\Api\AuditLogController;

// --- RUTE POSTMAN/REACT ---

// Kategori (Full CRUD)
Route::apiResource('kategori', KategoriController::class); 

// BARANG (Master Data - SEKARANG MENDUKUNG STORE/POST)
// Baris ini yang diperbaiki: dihapus ->except(['store'])
Route::apiResource('barang', BarangController::class); 

// User Management (Admin Only)
Route::apiResource('user', UserController::class)->only(['index', 'store', 'update', 'destroy']); 

// Rute Transaksi (Index dan Store)
Route::apiResource('transaksi-masuk', TransaksiMasukController::class)->only(['index', 'store']); 
Route::apiResource('transaksi-keluar', TransaksiKeluarController::class)->only(['index', 'store']); 

// Rute History
Route::get('audit-logs', [AuditLogController::class, 'index']);