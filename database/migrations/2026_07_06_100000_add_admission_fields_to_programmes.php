<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programmes', function (Blueprint $table) {
            $table->decimal('total_credit_hours', 6, 1)->nullable()->after('level');
            $table->string('duration')->nullable()->after('total_credit_hours');
            $table->text('description')->nullable()->after('duration');
            $table->decimal('tuition_fee', 12, 2)->nullable()->after('description');
            $table->decimal('application_fee', 12, 2)->nullable()->after('tuition_fee');
            $table->decimal('registration_fee', 12, 2)->nullable()->after('application_fee');
            $table->decimal('other_fees', 12, 2)->nullable()->after('registration_fee');
            $table->text('entry_requirements')->nullable()->after('other_fees');
            $table->text('required_grades')->nullable()->after('entry_requirements');
            $table->unsignedSmallInteger('max_intake')->nullable()->after('required_grades');
            $table->date('application_closing_date')->nullable()->after('max_intake');
            $table->boolean('applications_open')->default(false)->after('application_closing_date');
        });
    }

    public function down(): void
    {
        Schema::table('programmes', function (Blueprint $table) {
            $table->dropColumn([
                'total_credit_hours', 'duration', 'description', 'tuition_fee',
                'application_fee', 'registration_fee', 'other_fees',
                'entry_requirements', 'required_grades', 'max_intake',
                'application_closing_date', 'applications_open',
            ]);
        });
    }
};
