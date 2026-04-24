<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('distribusi_detail')) {
            Schema::create('distribusi_detail', function (Blueprint $table) {
                $table->id();
                $table->foreignId('distribusi_id')->constrained('distribusi')->cascadeOnDelete();
                $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
                $table->integer('jumlah');
                $table->decimal('harga', 10, 2);
                $table->decimal('subtotal', 10, 2);
                $table->timestamps();
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('distribusi_detail');
    }
};
