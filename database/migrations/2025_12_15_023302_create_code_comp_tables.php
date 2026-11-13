<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('no name');
            $table->boolean('isActive')->default(false);
            $table->timestamps();
        });

        Schema::create('candidates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');

            $table->string('fullname')->default('no name');
            $table->string('group')->default('none');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('challenges', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');

            $table->string('title')->default('A Challenge')->nullable(false);
            $table->text('description');
            $table->float('points')->default(100);
            $table->enum('difficulty', ['easy', 'medium', 'hard', 'challenge'])->default('easy');
            $table->enum('language', ['java', 'python'])->default('java');
            $table->text('code_solution')->nullable(false);
            $table->timestamps();
        });

        Schema::create('submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->foreignId('challenge_id')->constrained('challenges')->onDelete('cascade');
            $table->float('score')->default(0);
            $table->text('code');
            $table->boolean('is_score_submitted')->default(0);

            $table->unique(['candidate_id', 'challenge_id']);

            $table->timestamps();
        });

        Schema::create('options', function (Blueprint $table) {
            $table->id();

            $table->string('option_name')->default('option_1');
            $table->enum('option_datatype', ['int', 'float', 'boolean', 'text'])->default('boolean');
            $table->text('option_value');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('challenges');
        Schema::dropIfExists('candidates');
        Schema::dropIfExists('events');
        Schema::dropIfExists('options');
    }
};
