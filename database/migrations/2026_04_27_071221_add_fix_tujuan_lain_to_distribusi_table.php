<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('distribusi')) {
            Schema::table('distribusi', function (Blueprint $table) {
                $table->string('tujuan_lain')->nullable()->after('reseller_id');
            });
        }
    }
    public function down(): void
    {
        Schema::table('distribusi', function (Blueprint $table) {
            //
        });
    }
};
