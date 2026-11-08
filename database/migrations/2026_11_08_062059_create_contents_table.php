<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin-edited portfolio content. One row per section and locale
     * ('all' for language-neutral sections); `value` maps data paths
     * (e.g. "banner.hero_title") to their saved values.
     */
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('section', 50);
            $table->string('locale', 5);
            $table->json('value');
            $table->timestamps();

            $table->unique(['section', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
