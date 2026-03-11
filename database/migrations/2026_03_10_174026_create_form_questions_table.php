<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')
                  ->constrained('forms')
                  ->cascadeOnDelete();

            // Type de champ : text_input, textarea, email, phone,
            //                 number_input, dropdown, checkbox, radio,
            //                 date_picker, file_upload
            $table->string('type');

            // Toutes les propriétés du champ (label, placeholder,
            // required, options, rows, min, max, maxLength, accept…)
            $table->json('properties')->nullable();

            // Position dans le formulaire
            $table->unsignedSmallInteger('order')->default(0);

            $table->timestamps();

            $table->index(['form_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_questions');
    }
};