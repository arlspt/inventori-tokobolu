<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_module_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('modul', ['pengadaan', 'produksi', 'distribusi', 'retur']);
            $table->boolean('dapat_akses')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'modul']); // 1 row per user per modul
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_module_permissions');
    }
};
