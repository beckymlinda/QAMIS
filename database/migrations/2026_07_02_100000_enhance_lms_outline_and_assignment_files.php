<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_outline_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['course_offering_id', 'type']);
        });

        Schema::table('lms_assignments', function (Blueprint $table) {
            $table->string('attachment_file_path')->nullable()->after('instructions');
        });

        Schema::table('lms_assignment_submissions', function (Blueprint $table) {
            $table->string('marked_file_path')->nullable()->after('file_path');
            $table->json('annotation_data')->nullable()->after('marked_file_path');
        });
    }

    public function down(): void
    {
        Schema::table('lms_assignment_submissions', function (Blueprint $table) {
            $table->dropColumn(['marked_file_path', 'annotation_data']);
        });

        Schema::table('lms_assignments', function (Blueprint $table) {
            $table->dropColumn('attachment_file_path');
        });

        Schema::dropIfExists('lms_outline_items');
    }
};
