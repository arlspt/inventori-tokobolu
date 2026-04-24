<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('retur_detail')) {
            Schema::create('retur_detail', function (Blueprint $table) {
                $table->id();
                $table->foreignId('retur_id')->constrained('retur')->cascadeOnDelete();
                $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
                $table->integer('jumlah');
                $table->string('alasan')->nullable();
                $table->timestamps();
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('retur_detail');
    }
};
