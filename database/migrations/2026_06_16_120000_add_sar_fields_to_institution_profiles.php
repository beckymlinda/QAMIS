<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institution_profiles', function (Blueprint $table) {
            $table->text('executive_summary')->nullable()->after('background_narrative');
            $table->text('abbreviations_acronyms')->nullable()->after('executive_summary');
            $table->text('introduction_approach')->nullable()->after('abbreviations_acronyms');
            $table->text('assessment_team_composition')->nullable()->after('introduction_approach');
            $table->text('core_function')->nullable()->after('assessment_team_composition');
            $table->text('policies_procedures_summary')->nullable()->after('core_function');
        });
    }

    public function down(): void
    {
        Schema::table('institution_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'executive_summary',
                'abbreviations_acronyms',
                'introduction_approach',
                'assessment_team_composition',
                'core_function',
                'policies_procedures_summary',
            ]);
        });
    }
};
