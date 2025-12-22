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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string("fullname")->default("no name");
            $table->string("group")->default("none");
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string("title")->default("A Challenge")->nullable(false);
            $table->text("description");
            $table->float("points")->default(100);
            $table->enum("difficulty", ['easy', 'medium', 'hard', 'challenge'])->default('easy');

            $table->text('challege_key_file_path')->nullable(false);
            $table->timestamps();
        });

        Schema::create('solved_challenges', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->foreignId('challenge_id')->constrained('challenges')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
        Schema::dropIfExists('challenges');
        Schema::dropIfExists('solved_challenges');
    }
};
