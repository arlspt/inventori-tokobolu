<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('resep')) {
            Schema::create('resep', function (Blueprint $table) {
                $table->id();
                $table->foreignId('produk_id'); // nanti bisa rename ke varian_id
                $table->foreignId('bahan_baku_id');
                $table->integer('jumlah'); // gram / ml
                $table->timestamps();
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('resep');
    }
};
