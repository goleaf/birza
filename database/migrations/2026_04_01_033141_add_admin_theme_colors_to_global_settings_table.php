<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->string('admin_primary_color', 7)
                ->default('#13261F')
                ->after('portal_additional_price');
            $table->string('admin_accent_color', 7)
                ->default('#D2FF72')
                ->after('admin_primary_color');
            $table->string('admin_surface_color', 7)
                ->default('#F4C16D')
                ->after('admin_accent_color');
        });
    }

    public function down(): void
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn([
                'admin_primary_color',
                'admin_accent_color',
                'admin_surface_color',
            ]);
        });
    }
};
