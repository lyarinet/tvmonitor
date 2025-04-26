<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('output_streams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('protocol')->comment('HLS, HTTP, DASH, RTSP, UDP');
            $table->string('url');
            $table->foreignId('multiview_layout_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('inactive')->comment('active, inactive, error');
            $table->json('ffmpeg_options')->nullable()->comment('Additional FFmpeg options for output');
            $table->json('metadata')->nullable()->comment('Additional stream information like resolution, bitrate, etc.');
            $table->json('error_log')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('output_streams');
    }
};
