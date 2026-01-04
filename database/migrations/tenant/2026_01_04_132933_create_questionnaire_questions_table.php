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
        Schema::create('questionnaire_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('question_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('order')->unsigned();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_unique')->default(false);
            $table->boolean('is_main')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->boolean('is_filterable_by_range')->default(false);
            $table->boolean('is_shown')->default(false);
            $table->boolean('is_hidable')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaire_questions');
    }
};
