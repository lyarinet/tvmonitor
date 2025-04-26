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
        Schema::create('input_streams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('protocol')->comment('HLS, HTTP, DASH, RTSP, UDP');
            $table->string('url');
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('status')->default('inactive')->comment('active, inactive, error');
            $table->text('metadata')->nullable()->comment('Additional stream information like resolution, bitrate, etc.');
            $table->text('error_log')->nullable();
            
            // Advanced UDP options
            $table->string('local_address')->nullable();
            $table->string('program_id')->nullable();
            $table->boolean('ignore_unknown')->default(false);
            $table->boolean('map_disable_data')->default(false);
            $table->boolean('map_disable_subtitles')->default(false);
            $table->text('additional_options')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('input_streams');
    }
};
