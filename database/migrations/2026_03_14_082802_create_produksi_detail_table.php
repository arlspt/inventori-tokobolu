<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('produksi_detail')) {
            Schema::create('produksi_detail', function (Blueprint $table) {
                $table->id();
                $table->foreignId('produksi_id')->constrained('produksi')->cascadeOnDelete();
                $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
                $table->integer('jumlah_produksi');
                $table->timestamps();
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('produksi_detail');
    }
};
