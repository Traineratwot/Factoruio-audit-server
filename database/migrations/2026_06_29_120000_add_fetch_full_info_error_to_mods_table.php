<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mods', function (Blueprint $table) {
            $table->text('fetch_full_info_error')->nullable()->after('fetch_full_info_at');
        });
    }

    public function down(): void
    {
        Schema::table('mods', function (Blueprint $table) {
            $table->dropColumn('fetch_full_info_error');
        });
    }
};
