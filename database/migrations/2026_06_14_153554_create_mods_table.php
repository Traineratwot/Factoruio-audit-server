<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('owner')->index();
            $table->string('latest_version')->nullable();
            $table->string('category')->index()->nullable();
            $table->text('title')->nullable();
            $table->longText('summary')->nullable();
            $table->integer('downloads_count')->nullable();
            $table->float('popularity')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mods');
    }
};
