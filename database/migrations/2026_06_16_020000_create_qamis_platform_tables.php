<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('acronym')->nullable();
            $table->unsignedSmallInteger('establishment_year')->nullable();
            $table->date('registered_at')->nullable();
            $table->date('accredited_at')->nullable();
            $table->string('web_address')->nullable();
            $table->json('thematic_focus')->nullable();
            $table->json('programme_levels')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('institution_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->dropUnique(['email']);
            $table->unique(['institution_id', 'email']);
        });

        Schema::create('campuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('address')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('user_role_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role_name');
            $table->foreignId('institution_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('scope_type')->nullable();
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'institution_id']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->string('event');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['institution_id', 'created_at']);
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('user_role_assignments');
        Schema::dropIfExists('campuses');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['institution_id', 'email']);
            $table->dropConstrainedForeignId('institution_id');
            $table->unique('email');
        });

        Schema::dropIfExists('institutions');
    }
};
