<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('distribusi')) {
            Schema::create('distribusi', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->foreignId('reseller_id')->constrained('reseller')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('nomor_invoice')->unique()->change();
                $table->decimal('total', 10, 2)->default(0);
                $table->timestamps();
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('distribusi');
    }
};
