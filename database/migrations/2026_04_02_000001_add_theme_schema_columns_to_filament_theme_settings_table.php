<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('filament_theme_settings')) {
            return;
        }

        Schema::table('filament_theme_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('filament_theme_settings', 'theme_mode')) {
                $table->string('theme_mode', 10)->default('auto')->after('accent_color');
            }

            if (! Schema::hasColumn('filament_theme_settings', 'preset')) {
                $table->string('preset', 100)->default('corporate')->after('theme_mode');
            }

            if (! Schema::hasColumn('filament_theme_settings', 'tokens')) {
                $table->json('tokens')->nullable()->after('preset');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('filament_theme_settings')) {
            return;
        }

        Schema::table('filament_theme_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('filament_theme_settings', 'tokens')) {
                $table->dropColumn('tokens');
            }

            if (Schema::hasColumn('filament_theme_settings', 'preset')) {
                $table->dropColumn('preset');
            }

            if (Schema::hasColumn('filament_theme_settings', 'theme_mode')) {
                $table->dropColumn('theme_mode');
            }
        });
    }
};
