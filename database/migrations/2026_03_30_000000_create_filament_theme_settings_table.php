<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('filament_theme_settings')) {
            return;
        }

        Schema::create('filament_theme_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('accent_color', 20)->default('#cba24c');
            $table->boolean('show_left_sidebar')->default(true);
            $table->boolean('compact_sidebar')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filament_theme_settings');
    }
};

