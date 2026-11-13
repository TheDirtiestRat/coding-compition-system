<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained('challenges')->onDelete('cascade');
            $table->string('photo_path');
            $table->timestamps();
        });

        Schema::create('test_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained('challenges')->onDelete('cascade');
            $table->text('input')->nullable();
            $table->text('expected_output');
            $table->float('points')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('programming_languages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('compile_command')->nullable();
            $table->string('run_command');
            $table->string('file_extension');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        if (Schema::hasColumn('challenges', 'photo')) {
            Schema::table('challenges', function (Blueprint $table) {
                $table->dropColumn('photo');
            });
        }

        Schema::table('challenges', function (Blueprint $table) {
            $table->string('language', 50)->change();
            $table->foreignId('language_id')->nullable()->constrained('programming_languages')->nullOnDelete();
        });

        if (! Schema::hasColumn('submissions', 'test_case_results')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->json('test_case_results')->nullable()->after('code');
                $table->boolean('is_auto_reviewed')->default(false)->after('is_score_submitted');
            });
        }
    }

    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropConstrainedForeignId('language_id');
            $table->string('photo')->nullable();
        });
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['test_case_results', 'is_auto_reviewed']);
        });

        Schema::dropIfExists('challenge_photos');
        Schema::dropIfExists('test_cases');
        Schema::dropIfExists('programming_languages');
    }
};
