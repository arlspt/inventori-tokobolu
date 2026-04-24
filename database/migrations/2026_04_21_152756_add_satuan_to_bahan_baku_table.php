<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bahan_baku')) {
            Schema::table('bahan_baku', function (Blueprint $table) {
                $table->enum('satuan', ['gram', 'ml'])->default('gram');
            });
        }
    }
    public function down(): void
    {
        Schema::table('bahan_baku', function (Blueprint $table) {
            $table->dropColumn('satuan');
        });
    }
};
