<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institution_website_settings', function (Blueprint $table) {
            $table->json('team_members')->nullable()->after('about_content');
        });
    }

    public function down(): void
    {
        Schema::table('institution_website_settings', function (Blueprint $table) {
            $table->dropColumn('team_members');
        });
    }
};
