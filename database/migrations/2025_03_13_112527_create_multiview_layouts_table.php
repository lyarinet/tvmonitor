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
        Schema::create('multiview_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('rows')->default(2);
            $table->integer('columns')->default(2);
            $table->integer('width')->default(1920);
            $table->integer('height')->default(1080);
            $table->string('background_color')->default('#000000');
            $table->string('status')->default('inactive')->comment('active, inactive');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('multiview_layouts');
    }
};
