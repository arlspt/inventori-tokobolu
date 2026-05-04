<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distribusi', function (Blueprint $table) {
            $table->string('status_pembayaran')
                ->default('belum_bayar')
                ->after('status');
        });
    }
    public function down(): void
    {
        Schema::table('distribusi', function (Blueprint $table) {});
    }
};
