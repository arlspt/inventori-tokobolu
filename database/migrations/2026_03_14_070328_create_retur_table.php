<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('retur')) {
            Schema::create('retur', function (Blueprint $table) {
                $table->id();
                $table->foreignId('distribusi_id')->constrained('distribusi')->cascadeOnDelete();
                $table->date('tanggal');
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('retur');
    }
};
