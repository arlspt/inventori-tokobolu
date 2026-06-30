<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produksi_detail', function (Blueprint $table) {
            // ✅ kolom gagal sudah ada atau belum?
            if (!Schema::hasColumn('produksi_detail', 'gagal')) {
                $table->integer('gagal')->default(0)->after('jumlah_produksi');
            }
            // ✅ tambah expired_at
            $table->date('expired_at')->nullable()->after('gagal');
        });
    }

    public function down(): void
    {
        Schema::table('produksi_detail', function (Blueprint $table) {
            $table->dropColumn('expired_at');
        });
    }
};