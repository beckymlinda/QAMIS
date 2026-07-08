<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_discussions', function (Blueprint $table) {
            $table->boolean('is_closed')->default(false)->after('is_pinned');
            $table->timestamp('closed_at')->nullable()->after('is_closed');
        });

        Schema::table('lms_discussion_posts', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('body');
            $table->string('file_name')->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('lms_discussion_posts', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'file_name']);
        });

        Schema::table('lms_discussions', function (Blueprint $table) {
            $table->dropColumn(['is_closed', 'closed_at']);
        });
    }
};
