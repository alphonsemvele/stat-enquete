<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('title')->default('Nouveau formulaire');
            $table->text('description')->nullable();

            // Apparence
            $table->string('color', 7)->default('#3B82F6');
            $table->string('cover_image')->nullable();

            // Comportement
            $table->boolean('is_published')->default(false);
            $table->boolean('accepts_responses')->default(true);
            $table->timestamp('closes_at')->nullable();

            // Confirmation
            $table->text('confirmation_message')->nullable();
            $table->string('redirect_url')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};