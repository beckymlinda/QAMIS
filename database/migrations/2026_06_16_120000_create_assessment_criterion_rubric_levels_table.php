<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_criterion_rubric_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_criterion_id');
            $table->unsignedTinyInteger('score');
            $table->string('level_label');
            $table->text('descriptor');
            $table->timestamps();

            $table->foreign('assessment_criterion_id', 'acr_levels_criterion_fk')
                ->references('id')->on('assessment_criteria')->cascadeOnDelete();
            $table->unique(['assessment_criterion_id', 'score'], 'criterion_rubric_level_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_criterion_rubric_levels');
    }
};
