<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('url')->index()->nullable();
            $table->string('method')->index()->nullable();
            $table->text('request_body')->nullable();
            $table->json('request_query')->nullable();
            $table->text('response_body')->nullable();
            $table->json('request_head')->nullable();
            $table->json('response_head')->nullable();
            $table->float('time')->nullable();
            $table->string('status_code')->index()->nullable();
            $table->boolean('completed')->default(false);
            $table->string('subject_type')->nullable();
            $table->bigInteger('subject_id')->nullable();
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_logs');
    }
};
