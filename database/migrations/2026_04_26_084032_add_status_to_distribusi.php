<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distribusi', function (Blueprint $table) {
            if (!Schema::hasColumn('distribusi', 'status')) {
                $table->string('status')
                    ->default('dikirim');
            }
        });
    }

    public function down(): void
    {
        Schema::table('distribusi', function (Blueprint $table) {
            if (Schema::hasColumn('distribusi', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
