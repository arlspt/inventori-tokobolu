<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('retur_detail', 'kondisi')) {
            Schema::table('retur_detail', function (Blueprint $table) {
                $table->string('kondisi')->nullable()->after('alasan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('retur_detail', 'kondisi')) {
            Schema::table('retur_detail', function (Blueprint $table) {
                $table->dropColumn('kondisi');
            });
        }
    }
};
