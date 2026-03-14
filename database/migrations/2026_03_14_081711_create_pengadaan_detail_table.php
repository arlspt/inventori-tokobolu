<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengadaan_detail', function (Blueprint $table) {
    $table->id();
    $table->foreignId('pengadaan_id')->constrained('pengadaan')->cascadeOnDelete();
    $table->foreignId('bahan_baku_id')->constrained('bahan_baku')->cascadeOnDelete();
    $table->integer('jumlah');
    $table->decimal('harga',10,2);
    $table->decimal('subtotal',10,2);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengadaan_detail');
    }
};
