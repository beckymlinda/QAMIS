<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_assignments', function (Blueprint $table) {
            $table->decimal('coursework_weight_percent', 5, 2)->default(0)->after('max_score');
        });

        Schema::table('course_results', function (Blueprint $table) {
            $table->boolean('use_final_override')->default(false)->after('final_percentage');
            $table->decimal('final_percentage_override', 5, 2)->nullable()->after('use_final_override');
        });
    }

    public function down(): void
    {
        Schema::table('lms_assignments', function (Blueprint $table) {
            $table->dropColumn('coursework_weight_percent');
        });

        Schema::table('course_results', function (Blueprint $table) {
            $table->dropColumn(['use_final_override', 'final_percentage_override']);
        });
    }
};
