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
        Schema::create('layout_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('multiview_layout_id')->constrained()->onDelete('cascade');
            $table->foreignId('input_stream_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('position_x');
            $table->integer('position_y');
            $table->integer('width');
            $table->integer('height');
            $table->integer('z_index')->default(0);
            $table->boolean('show_label')->default(true);
            $table->string('label_position')->default('bottom')->comment('top, bottom, left, right');
            $table->json('overlay_options')->nullable()->comment('Options for text overlay, borders, etc.');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layout_positions');
    }
};
