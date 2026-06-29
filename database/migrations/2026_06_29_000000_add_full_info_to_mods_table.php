<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mods', function (Blueprint $table) {
            $table->string('thumbnail')->nullable()->after('summary');
            $table->longText('description')->nullable()->after('summary');
            $table->string('homepage')->nullable()->after('description');
            $table->json('license')->nullable()->after('homepage');
            $table->json('tags')->nullable()->after('license');
            $table->json('images')->nullable()->after('tags');
            $table->longText('changelog')->nullable()->after('releases');
            $table->float('score')->nullable()->after('changelog');
            $table->string('factorio_version')->nullable()->after('score');
            $table->timestamp('latest_release_date')->nullable()->after('factorio_version');
        });
    }

    public function down(): void
    {
        Schema::table('mods', function (Blueprint $table) {
            $table->dropColumn([
                'thumbnail',
                'description',
                'homepage',
                'license',
                'tags',
                'images',
                'releases',
                'changelog',
                'score',
                'factorio_version',
                'latest_release_date',
            ]);
        });
    }
};
