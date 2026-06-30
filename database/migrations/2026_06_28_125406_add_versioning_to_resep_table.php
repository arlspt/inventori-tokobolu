<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resep', function (Blueprint $table) {
            $table->integer('versi')->default(1)->after('jumlah');
            $table->date('berlaku_dari')->nullable()->after('versi');
            $table->boolean('aktif')->default(true)->after('berlaku_dari');
        });
    }

    public function down(): void
    {
        Schema::table('resep', function (Blueprint $table) {
            $table->dropColumn(['versi', 'berlaku_dari', 'aktif']);
        });
    }
};
