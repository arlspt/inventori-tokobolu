<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('retur_detail', function (Blueprint $table) {
            $table->dropColumn('kondisi');
        });
    }

    public function down(): void
    {
        Schema::table('retur_detail', function (Blueprint $table) {
            $table->string('kondisi')->nullable()->after('alasan_lain');
        });
    }
};
