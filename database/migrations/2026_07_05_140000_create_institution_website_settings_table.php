<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institution_website_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->boolean('is_published')->default(false);
            $table->string('school_name')->nullable();
            $table->string('tagline')->nullable();
            $table->text('hero_description')->nullable();
            $table->json('hero_features')->nullable();
            $table->text('about_content')->nullable();
            $table->text('programs_intro')->nullable();
            $table->text('application_intro')->nullable();
            $table->text('application_payment_instructions')->nullable();
            $table->text('application_requirements')->nullable();
            $table->unsignedSmallInteger('application_upload_max_mb')->default(10);
            $table->string('footer_address')->nullable();
            $table->string('footer_phone')->nullable();
            $table->string('footer_email')->nullable();
            $table->text('footer_extra')->nullable();
            $table->string('primary_color', 7)->default('#0f2744');
            $table->string('secondary_color', 7)->default('#8cc63f');
            $table->string('logo_path')->nullable();
            $table->json('slider_images')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institution_website_settings');
    }
};
