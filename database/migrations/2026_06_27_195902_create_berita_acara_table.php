<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berita_acara', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('modul', ['bahan_baku', 'produksi']);
            $table->enum('topik', ['bahan_baku', 'hasil_produksi']);
            $table->enum('kategori', ['hilang', 'rusak', 'cacat', 'tumpah', 'kadaluarsa', 'lainnya']);
            $table->string('nama_item'); // nama bahan baku atau nama produk
            $table->decimal('jumlah', 10, 2); // jumlah yang terdampak
            $table->string('satuan')->nullable(); // gram, ml, pcs, dll
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berita_acara');
    }
};