<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_response_id')
                  ->constrained('form_responses')
                  ->cascadeOnDelete();
            $table->foreignId('form_question_id')
                  ->constrained('form_questions')
                  ->cascadeOnDelete();

            // Valeur texte pour les champs simples (text, email, date…)
            // JSON pour les champs multi-valeurs (checkboxes, fichiers…)
            $table->text('value')->nullable();

            $table->timestamps();

            $table->index(['form_response_id', 'form_question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_answers');
    }
};