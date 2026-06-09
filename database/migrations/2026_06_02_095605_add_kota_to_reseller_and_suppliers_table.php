<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reseller', function (Blueprint $table) {
            $table->string('kota')->nullable()->after('alamat');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('kota')->nullable()->after('alamat');
        });
    }

    public function down(): void
    {
        Schema::table('reseller', function (Blueprint $table) {
            $table->dropColumn('kota');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('kota');
        });
    }
};
